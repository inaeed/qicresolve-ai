<?php

namespace App\Http\Controllers;

use App\Models\QicIssue;
use App\Services\QicIssueSlaService;
use App\Services\QicQualityInsightService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class QicQualityInsightController extends Controller
{
    public function __invoke(
        Request $request,
        QicIssueSlaService $sla,
        QicQualityInsightService $ai
    ): JsonResponse {
        abort_unless(
            $request->user()->canAccessWorkspace('quality'),
            403
        );

        $data = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $timezone = config('qic_sla.timezone');

        $from = CarbonImmutable::parse($data['from'], $timezone)
            ->startOfDay();

        $until = CarbonImmutable::parse($data['to'], $timezone)
            ->addDay()
            ->startOfDay();

        // Timestamp database mengikuti timezone aplikasi.
        $databaseTimezone = config('app.timezone', 'UTC');

        $query = QicIssue::visibleTo($request->user())
            ->where('created_at', '>=', $from->setTimezone($databaseTimezone))
            ->where('created_at', '<', $until->setTimezone($databaseTimezone));

        $count = (clone $query)->count();

        if ($count > 50) {
            return response()->json([
                'message' => 'Periode berisi lebih dari 50 issue. '
                    .'Persempit periode agar semua data dapat dianalisis.',
            ], 422);
        }

        if ($count === 0) {
            return response()->json([
                'answer' => 'Tidak ada issue yang dapat diakses '
                    .'pada periode tanggal pembuatan tersebut.',
                'references' => [],
                'issue_count' => 0,
            ]);
        }

        $records = $query->orderBy('id')->get();
        $evaluatedAt = now();
        $issues = [];

        foreach ($records as $issue) {
            $summary = $sla->summarize($issue, $evaluatedAt);

            $issues[] = [
                'code' => $issue->code,
                'status' => $issue->status,
                'material' => $issue->material,
                'defect' => $issue->defect,
                'quantity_reject' => $issue->quantity,
                'sla' => collect($summary['measurements'])->map(
                    fn ($row) => collect($row)->only([
                        'rule_id',
                        'name',
                        'cycle',
                        'actual_minutes',
                        'target_minutes',
                        'overdue_minutes',
                        'status',
                        'measurement_state',
                    ])->all()
                )->all(),
                'warnings' => $summary['warnings'],
            ];
        }

        $measurements = collect($issues)->flatMap(fn ($row) => $row['sla']);

        $context = [
            'period' => [
                'from' => $data['from'],
                'to' => $data['to'],
                'basis' => 'Tanggal pembuatan issue, WIB',
            ],
            'evaluated_at' => $evaluatedAt->toIso8601String(),
            'summary' => [
                'issue_count' => $records->count(),
                'issues_by_status' => $records->countBy('status')->all(),
                'issues_by_defect' => $records->countBy('defect')->all(),
                'sla_measurements_by_status' => $measurements->countBy('status')->all(),
                'issues_with_any_violation' => collect($issues)->filter(
                    fn ($row) => collect($row['sla'])
                        ->contains('status', 'violation')
                )->count(),
            ],
            'issues' => $issues,
        ];

        // Tolak konteks terlalu besar, jangan memotong data diam-diam.
        if (strlen(json_encode($context, JSON_THROW_ON_ERROR)) > 100000) {
            return response()->json([
                'message' => 'Riwayat pada periode ini terlalu banyak. '
                    .'Pilih periode yang lebih pendek.',
            ], 422);
        }

        try {
            $result = $ai->answer($data['question'], $context);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => 'AI Insight belum berhasil menjawab. '
                    .'Coba lagi nanti. Monitoring SLA tetap bisa digunakan.',
            ], 503);
        }

        return response()->json([
            'answer' => $result['answer'],
            'issue_count' => $records->count(),
            'references' => collect($result['issue_codes'])->map(
                fn ($code) => [
                    'code' => $code,
                    'url' => route('quality.issues.show', ['issue' => $code]),
                ]
            )->all(),
        ]);
    }
}
