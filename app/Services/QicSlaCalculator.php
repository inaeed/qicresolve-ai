<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class QicSlaCalculator
{
    public function __construct(
        private QicWorkingCalendar $calendar
    ) {}

    /**
     * @return array{
     *     rule_id: string,
     *     rule_version: mixed,
     *     name: mixed,
     *     clock: 'working'|'calendar',
     *     started_at: string,
     *     finished_at: string|null,
     *     evaluated_at: string,
     *     completed: bool,
     *     target_minutes: mixed,
     *     actual_seconds: int,
     *     actual_minutes: float,
     *     remaining_minutes: float,
     *     overdue_minutes: float,
     *     status: 'violation'|'compliant'|'at_risk'|'on_track'
     * }
     */
    public function evaluate(
        string $ruleId,
        DateTimeInterface $startedAt,
        ?DateTimeInterface $finishedAt = null,
        ?DateTimeInterface $asOf = null
    ): array {
        $rule = config("qic_sla.rules.{$ruleId}");

        if (! is_array($rule)) {
            throw new InvalidArgumentException('Aturan SLA tidak ditemukan.');
        }

        $start = DateTimeImmutable::createFromInterface($startedAt);

        // Waktu evaluasi bisa diberikan agar pengujian konsisten.
        $end = DateTimeImmutable::createFromInterface(
            $finishedAt ?? $asOf ?? new DateTimeImmutable
        );

        if ($end < $start) {
            throw new InvalidArgumentException(
                'Waktu evaluasi tidak boleh sebelum waktu mulai.'
            );
        }

        $targetSeconds = (int) $rule['target_minutes'] * 60;

        if ($targetSeconds <= 0) {
            throw new InvalidArgumentException('Target SLA harus positif.');
        }

        $actualSeconds = match ($rule['clock']) {
            'working' => $this->calendar->secondsBetween($start, $end),
            'calendar' => $end->getTimestamp() - $start->getTimestamp(),
            default => throw new InvalidArgumentException(
                'Jenis kalender SLA tidak dikenali.'
            ),
        };

        $completed = $finishedAt !== null;

        // Bandingkan detik asli, bukan angka menit yang dibulatkan.
        $status = match (true) {
            $actualSeconds > $targetSeconds => 'violation',
            $completed => 'compliant',
            $actualSeconds > $targetSeconds * 0.75 => 'at_risk',
            default => 'on_track',
        };

        return [
            'rule_id' => $ruleId,
            'rule_version' => config('qic_sla.version'),
            'name' => $rule['name'],
            'clock' => $rule['clock'],
            'started_at' => $start->format(DATE_ATOM),
            'finished_at' => $completed
                ? $end->format(DATE_ATOM)
                : null,
            'evaluated_at' => $end->format(DATE_ATOM),
            'completed' => $completed,
            'target_minutes' => $rule['target_minutes'],
            'actual_seconds' => $actualSeconds,
            'actual_minutes' => round($actualSeconds / 60, 2),
            'remaining_minutes' => round(
                max(0, $targetSeconds - $actualSeconds) / 60,
                2
            ),
            'overdue_minutes' => round(
                max(0, $actualSeconds - $targetSeconds) / 60,
                2
            ),
            'status' => $status,
        ];
    }
}
