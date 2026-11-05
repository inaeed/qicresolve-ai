<section class="qic-card">
    <h2>Monitoring SLA</h2>

    <p class="qic-small qic-muted">
        Perhitungan berdasarkan audit aktivitas dan aturan SLA simulasi.
        Waktu ditampilkan dalam WIB.
        Evaluasi ini belum merupakan snapshot histori permanen.
    </p>
</section>

@php
    $slaLabels = [
        'on_track' => 'On Track',
        'at_risk' => 'At Risk',
        'compliant' => 'SLA Compliant',
        'violation' => 'SLA Violation',
    ];

    $stateLabels = [
        'running' => 'Berjalan',
        'completed' => 'Selesai',
        'stopped' => 'Dihentikan',
    ];

    $slaColors = [
        'on_track' => '#075985',
        'at_risk' => '#92400e',
        'compliant' => '#166534',
        'violation' => '#b91c1c',
    ];
@endphp

@forelse($records as $record)
    @php
        $summary = $slaSummaries[$record->id] ?? [];
        $measurements = $summary['measurements'] ?? [];
    @endphp

    <section class="qic-card">
        <h2>
            <a href="{{ route('quality.issues.show', ['issue' => $record->code]) }}">
                {{ $record->code }}
            </a>
        </h2>

        <p>{{ $record->material_name ?? $record->material }}</p>

        <p class="qic-small qic-muted">
            Status issue:
            {{ $statuses[$record->status][0] ?? $record->status }}
            · Pemilik proses:
            {{ $record->owner?->name ?? 'Belum ditugaskan' }}
        </p>

        @if($measurements)
            <div
                style="overflow-x:auto"
                role="region"
                aria-label="Pengukuran SLA {{ $record->code }}"
                tabindex="0"
            >
                <table style="width:100%;border-collapse:collapse;min-width:760px">
                    <thead>
                        <tr>
                            @foreach([
                                'Tahap',
                                'Siklus',
                                'Mulai / Selesai WIB',
                                'Target',
                                'Aktual',
                                'Status'
                            ] as $heading)
                                <th style="text-align:left;padding:12px;border-bottom:1px solid #dbe6eb">
                                    {{ $heading }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($measurements as $row)
                            <tr>
                                <td style="padding:12px;border-bottom:1px solid #dbe6eb">
                                    <strong>{{ $row['rule_id'] }}</strong><br>
                                    {{ $row['name'] }}<br>
                                    <small>
                                        {{ $row['clock'] === 'working'
                                            ? 'Jam kerja'
                                            : 'Waktu kalender' }}
                                    </small>
                                </td>

                                <td style="padding:12px;border-bottom:1px solid #dbe6eb">
                                    {{ $row['cycle'] }}
                                </td>

                                <td style="padding:12px;border-bottom:1px solid #dbe6eb">
                                    {{ \Carbon\CarbonImmutable::parse($row['started_at'])
                                        ->setTimezone('Asia/Jakarta')
                                        ->format('d/m/Y H:i') }}
                                    <br>

                                    @if($row['finished_at'])
                                        {{ \Carbon\CarbonImmutable::parse($row['finished_at'])
                                            ->setTimezone('Asia/Jakarta')
                                            ->format('d/m/Y H:i') }}
                                    @elseif(!empty($row['stopped_at']))
                                        Dihentikan:
                                        {{ \Carbon\CarbonImmutable::parse($row['stopped_at'])
                                            ->setTimezone('Asia/Jakarta')
                                            ->format('d/m/Y H:i') }}
                                    @else
                                        Masih berjalan
                                    @endif
                                </td>

                                <td style="padding:12px;border-bottom:1px solid #dbe6eb">
                                    {{ number_format($row['target_minutes'] / 60, 2, ',', '.') }}
                                    jam
                                </td>

                                <td style="padding:12px;border-bottom:1px solid #dbe6eb">
                                    {{ number_format($row['actual_minutes'] / 60, 2, ',', '.') }}
                                    jam

                                    @if($row['overdue_minutes'] > 0)
                                        <br>
                                        <small style="color:#b91c1c">
                                            Lewat
                                            {{ number_format($row['overdue_minutes'], 2, ',', '.') }}
                                            menit
                                        </small>
                                    @endif
                                </td>

                                <td style="padding:12px;border-bottom:1px solid #dbe6eb">
                                    <strong style="color:{{ $slaColors[$row['status']] ?? '#475569' }}">
                                        {{ $slaLabels[$row['status']] ?? $row['status'] }}
                                    </strong>
                                    <br>
                                    <small>
                                        {{ $stateLabels[$row['measurement_state']]
                                            ?? $row['measurement_state'] }}
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p>Belum ada pengukuran SLA yang dapat ditampilkan.</p>
        @endif

        @foreach($summary['warnings'] ?? [] as $warning)
            <p class="qic-warning">{{ $warning }}</p>
        @endforeach
    </section>
@empty
    <section class="qic-card">
        <p>Belum ada issue yang sesuai dengan akses dan filter saat ini.</p>
    </section>
@endforelse