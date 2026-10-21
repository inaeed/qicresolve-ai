<div class="qic-role-intro"><h2>Perjalanan Complaint</h2><p>Pantau tahap penanganan berdasarkan status yang tersimpan. Garis tahap bukan perhitungan SLA.</p></div>
<div class="qic-stage-list">
@forelse($records as $row)
@php
$stage=match($row->status){'draft'=>0,'waiting_verification','need_information','rejected'=>1,'verified','assigned'=>2,'investigation','waiting_review','revision_requested','three_c_rejected'=>3,'approved','corrective','waiting_final'=>4,'closed'=>5,default=>0};
@endphp
<article class="qic-stage"><header><strong>{{ $row->code }} · {{ $row->material }}</strong><span class="qic-badge {{ $statuses[$row->status][1] }}">{{ $statuses[$row->status][0] }}</span></header><div class="qic-stage-track" aria-hidden="true">@for($i=1;$i<=5;$i++)<span class="{{ $i<=$stage?'is-done':'' }}"></span>@endfor</div><p class="qic-small">Pelaporan → Verifikasi / Penugasan → Investigasi / 3C → Tindakan → Selesai</p><p>{{ $row->defect }} · Pemilik proses: {{ $row->owner?->name ?? 'Belum ditugaskan' }}</p><a href="{{ route($qicRole.'.issues.show',['issue'=>$row->code]) }}">Lihat evidence dan riwayat →</a></article>
@empty<p class="qic-card">Belum ada complaint untuk dilacak.</p>@endforelse
</div>
