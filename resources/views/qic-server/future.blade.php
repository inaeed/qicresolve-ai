@php
$content=match($qicPage){
'sla-monitoring'=>['Monitoring SLA','Aturan SLA belum dikonfigurasi','Durasi dan pelanggaran SLA akan ditampilkan setelah target waktu dan aturan jam kerja disepakati.','Data yang tersedia saat ini: waktu kejadian, pembaruan issue, dan riwayat tindakan.'],
'ai-insights'=>['AI Insight','Analisis AI belum diaktifkan','Rekomendasi akan tersedia setelah integrasi model dan pembatasan penggunaan evidence selesai. Keputusan tetap pada Quality.','Complaint, versi 3C, dan evidence dapat ditinjau secara manual melalui detail issue.'],
default=>['Master Data','Pengelolaan master belum diaktifkan','Halaman ini akan digunakan untuk mengelola referensi material, defect, dan aturan proses.','Nilai material dan defect saat ini berasal dari complaint dan paket simulasi.']};
@endphp
<div class="qic-role-intro"><h2>{{ $content[0] }}</h2><p>{{ $content[2] }}</p></div><section class="qic-card"><h3>{{ $content[1] }}</h3><p>{{ $content[3] }}</p><a class="qic-button qic-secondary" href="{{ route('quality.issues.index') }}">Tinjau Data Issue</a></section>
@if($qicPage==='master-data')<div class="qic-menu-grid"><section class="qic-card"><h3>Material dalam Complaint</h3><ul>@foreach($overview->pluck('material')->unique() as $material)<li>{{ $material }}</li>@endforeach</ul></section><section class="qic-card"><h3>Jenis Defect dalam Complaint</h3><ul>@foreach($overview->pluck('defect')->unique() as $defect)<li>{{ $defect }}</li>@endforeach</ul></section></div>@endif
