<?php

namespace App\Services;

use App\Models\QicIssue;
use Illuminate\Support\Facades\DB;

class QicSlaSnapshotService
{
    public function capture(QicIssue $issue): int
    {
        return DB::transaction(function () use ($issue) {
            // Serialisasi penyimpanan snapshot untuk issue yang sama.
            $lockedIssue = QicIssue::whereKey($issue->id)
                ->lockForUpdate()
                ->firstOrFail();

            $summary = app(QicIssueSlaService::class)
                ->summarize($lockedIssue);

            $created = 0;

            foreach ($summary['measurements'] as $row) {
                if ($row['measurement_state'] !== 'completed') {
                    continue;
                }

                $key = [
                    'issue_id' => $lockedIssue->id,
                    'rule_id' => $row['rule_id'],
                    'cycle' => $row['cycle'],
                ];

                // Snapshot yang sudah ada tidak ditimpa.
                if (DB::table('qic_sla_snapshots')->where($key)->exists()) {
                    continue;
                }

                DB::table('qic_sla_snapshots')->insert([
                    ...$key,
                    'rule_version' => $row['rule_version'],
                    'rule_snapshot' => json_encode(
                        config("qic_sla.rules.{$row['rule_id']}"),
                        JSON_THROW_ON_ERROR
                    ),
                    'calendar_snapshot' => json_encode([
                        'timezone' => config('qic_sla.timezone'),
                        'schedule' => config('qic_sla.schedule'),
                        'breaks' => config('qic_sla.breaks'),
                    ], JSON_THROW_ON_ERROR),
                    'evaluation' => json_encode(
                        $row,
                        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                    ),
                    'source' => 'reconstructed_from_audit',
                    'captured_at' => now(),
                ]);

                $created++;
            }

            return $created;
        });
    }
}
