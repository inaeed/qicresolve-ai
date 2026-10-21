@php
$roleInfo = [
 'main-assy'=>['Pantau complaint Anda','Laporkan temuan defect, lengkapi bukti, dan ikuti perkembangan penanganan.',['Complaint Saya'=>$overview->count(),'Perlu Dilengkapi'=>$overview->whereIn('status',['draft','need_information'])->count(),'Dalam Proses'=>$overview->whereNotIn('status',['draft','closed','rejected'])->count(),'Selesai'=>$overview->where('status','closed')->count()]],
 'auto-line'=>['Pusat Penanganan Auto Line','Terima penugasan, simpan investigasi dan containment, lalu susun respons 3C.',['Total Penugasan'=>$overview->count(),'Belum Diterima'=>$overview->where('status','assigned')->count(),'Perlu Respons / Revisi'=>$overview->whereIn('status',['investigation','revision_requested','three_c_rejected'])->count(),'Tindakan Korektif'=>$overview->whereIn('status',['approved','corrective'])->count()]],
 'quality'=>['Pusat Kendali Quality','Verifikasi complaint, tentukan pemilik proses, dan tinjau hasil penanganan.',['Total Issue'=>$overview->count(),'Menunggu Verifikasi'=>$overview->where('status','waiting_verification')->count(),'Menunggu Review 3C'=>$overview->where('status','waiting_review')->count(),'Verifikasi Akhir'=>$overview->where('status','waiting_final')->count()]],
][$qicRole];
$priorityStatuses = match($qicRole){'main-assy'=>['draft','need_information'],'auto-line'=>['assigned','investigation','revision_requested','three_c_rejected','approved','corrective'],default=>['waiting_verification','verified','waiting_review','waiting_final']};
@endphp
<div class="qic-role-intro"><h2>{{ $roleInfo[0] }}</h2><p>{{ $roleInfo[1] }}</p></div>
<div class="qic-stats">@foreach($roleInfo[2] as $label=>$total)<article class="qic-card qic-stat"><p>{{ $label }}</p><strong>{{ $total }}</strong><p class="qic-small qic-muted">Data database sesuai hak akses</p></article>@endforeach</div>
<div class="qic-page-links">
@if($qicRole==='main-assy')<a class="qic-button" href="{{ route('main-assy.issues.create') }}">+ Buat Complaint</a><a class="qic-button qic-secondary" href="{{ route('main-assy.tracking') }}">Tracking Issue</a>
@elseif($qicRole==='auto-line')<a class="qic-button" href="{{ route('auto-line.issues.index') }}">Penugasan Saya</a><a class="qic-button qic-secondary" href="{{ route('auto-line.responses.index') }}">Respons 3C</a>
@else<a class="qic-button" href="{{ route('quality.assignments') }}">Penugasan Auto Line</a><a class="qic-button qic-secondary" href="{{ route('quality.reviews.index') }}">Review 3C</a>@endif
</div>
@include('qic-server.issue-list',['records'=>$overview->whereIn('status',$priorityStatuses)->take(5),'listTitle'=>'Prioritas Tindak Lanjut'])
@include('qic-server.issue-list',['records'=>$overview->take(5),'listTitle'=>'Issue Terbaru'])
