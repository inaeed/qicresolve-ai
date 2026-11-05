<?php

namespace App\Services;

use App\Models\QicIssue;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class QicIssueSlaService
{
    public function __construct(
        private QicSlaCalculator $calculator
    ) {}

    /**
     * @return array{
     *     issue_code: string,
     *     evaluated_at: string,
     *     calculation_mode: 'preview_from_audits',
     *     measurements: list<array<string, mixed>>,
     *     warnings: list<string>
     * }
     */
    public function summarize(
        QicIssue $issue,
        ?DateTimeInterface $asOf = null
    ): array {
        $asOf = DateTimeImmutable::createFromInterface(
            $asOf ?? now()
        );

        // Urutan ID mengikuti urutan transaksi workflow.
        $audits = $issue->audits()
            ->reorder()
            ->orderBy('id')
            ->get();

        $open = [];
        $rows = [];
        $cycles = [];
        $firstSubmission = null;
        $firstThreeC = false;
        $warnings = [];

        $start = function (
            string $ruleId,
            DateTimeInterface $time
        ) use (&$open, &$cycles): void {
            if (isset($open[$ruleId])) {
                return;
            }

            $cycles[$ruleId] = ($cycles[$ruleId] ?? 0) + 1;

            $open[$ruleId] = [
                'started_at' => $time,
                'cycle' => $cycles[$ruleId],
            ];
        };

        $finish = function (
            string $ruleId,
            DateTimeInterface $time
        ) use (&$open, &$rows): void {
            if (! isset($open[$ruleId])) {
                return;
            }

            $measurement = $open[$ruleId];

            $rows[] = [
                ...$this->calculator->evaluate(
                    $ruleId,
                    $measurement['started_at'],
                    $time
                ),
                'cycle' => $measurement['cycle'],
                'measurement_state' => 'completed',
            ];

            unset($open[$ruleId]);
        };

        foreach ($audits as $audit) {
            $time = $audit->created_at;

            if (! $time || $time > $asOf) {
                continue;
            }

            $from = $audit->from_status;
            $to = $audit->to_status;
            $action = $audit->action;

            // Lewati audit AI atau penyimpanan tanpa transisi status.
            if ($from === $to) {
                continue;
            }

            if ($to === 'waiting_verification') {
                $start('SLA-01', $time);

                if ($firstSubmission === null) {
                    $firstSubmission = $time;
                    $start('SLA-09', $time);
                }
            }

            if (
                $from === 'waiting_verification'
                && in_array($to, [
                    'verified', 'need_information', 'rejected',
                ], true)
            ) {
                $finish('SLA-01', $time);
            }

            if ($action === 'verify' && $to === 'verified') {
                $start('SLA-02', $time);
            }

            if ($action === 'assign' && $to === 'assigned') {
                $finish('SLA-02', $time);
                $start('SLA-03', $time);

                if (! $firstThreeC) {
                    $start('SLA-04', $time);
                }
            }

            if (
                $from === 'assigned'
                && $to === 'investigation'
            ) {
                $finish('SLA-03', $time);
            }

            if (
                $action === 'submit_3c'
                && $to === 'waiting_review'
            ) {
                if (! $firstThreeC) {
                    $finish('SLA-04', $time);
                    $firstThreeC = true;
                }

                $finish('SLA-06', $time);
                $start('SLA-05', $time);
            }

            if (
                $from === 'waiting_review'
                && in_array($action, [
                    'approve', 'revise', 'reject_3c',
                ], true)
            ) {
                $finish('SLA-05', $time);
            }

            if ($action === 'revise') {
                $start('SLA-06', $time);
            }

            if ($action === 'approve') {
                $start('SLA-07', $time);
            }

            if (
                $action === 'corrective'
                && $to === 'waiting_final'
            ) {
                $finish('SLA-07', $time);
                $start('SLA-08', $time);
            }

            if ($action === 'return_corrective') {
                // Dokumen belum menetapkan timer khusus pengerjaan ulang.
                // Timer closure tetap berjalan sampai issue ditutup.
                $warnings[] =
                    'Tindakan korektif dikembalikan. SLA-08 tetap berjalan; '
                    .'timer pengerjaan ulang belum ditetapkan.';
            }

            if ($action === 'close' && $to === 'closed') {
                $finish('SLA-08', $time);
                $finish('SLA-09', $time);
            }

            if ($action === 'reject' && $to === 'rejected') {
                // Penghentian bukan penyelesaian end-to-end.
                // Evaluasi terakhir tetap tersedia, tetapi bukan KPI selesai.
                foreach ($open as $ruleId => $measurement) {
                    $rows[] = [
                        ...$this->calculator->evaluate(
                            $ruleId,
                            $measurement['started_at'],
                            null,
                            $time
                        ),
                        'cycle' => $measurement['cycle'],
                        'measurement_state' => 'stopped',
                        'stopped_at' => $time->format(DATE_ATOM),
                        'stop_reason' => 'Issue ditolak.',
                    ];
                }

                $open = [];
            }
        }

        foreach ($open as $ruleId => $measurement) {
            $rows[] = [
                ...$this->calculator->evaluate(
                    $ruleId,
                    $measurement['started_at'],
                    null,
                    $asOf
                ),
                'cycle' => $measurement['cycle'],
                'measurement_state' => 'running',
            ];
        }

        if ($firstSubmission === null) {
            $warnings[] =
                'Audit submit complaint belum tersedia. '
                .'SLA tidak dihitung dari created_at issue secara otomatis.';
        }

        $snapshots = DB::table('qic_sla_snapshots')
            ->where('issue_id', $issue->id)
            ->get()
            ->keyBy(fn ($snapshot) => $snapshot->rule_id.':'.$snapshot->cycle
            );

        foreach ($rows as &$row) {
            $key = $row['rule_id'].':'.$row['cycle'];
            $snapshot = $snapshots->get($key);

            if ($snapshot) {
                /** @var array<string, mixed> $evaluation */
                $evaluation = json_decode(
                    $snapshot->evaluation,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                $row = [
                    ...$evaluation,
                    'snapshot_source' => $snapshot->source,
                    'snapshot_captured_at' => $snapshot->captured_at,
                ];
            }
        }

        unset($row);

        return [
            'issue_code' => $issue->code,
            'evaluated_at' => $asOf->format(DATE_ATOM),
            'calculation_mode' => 'preview_from_audits',
            'measurements' => $rows,
            'warnings' => array_values(array_unique($warnings)),
        ];
    }
}
