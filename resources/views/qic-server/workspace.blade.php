@extends('layouts.qic')
@section('workspace')
<style>
.qic-role-intro{padding:22px;border:1px solid #badde1;border-radius:16px;background:linear-gradient(115deg,#eef8fa,#fff);margin-bottom:22px}.qic-role-intro h2{margin:0 0 8px}.qic-menu-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-bottom:22px}.qic-menu-grid .qic-card{margin:0}.qic-menu-grid h3{margin-top:0}.qic-stage-list{display:grid;gap:12px}.qic-stage{padding:16px;border:1px solid #dbe6eb;border-radius:12px}.qic-stage header{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}.qic-stage-track{display:flex;gap:5px;margin:14px 0}.qic-stage-track span{flex:1;height:6px;background:#e2e8f0;border-radius:8px}.qic-stage-track span.is-done{background:#087f8c}.qic-report-count{font-size:30px;font-weight:700;color:#125166}.qic-event{padding:16px 0;border-bottom:1px solid #dbe6eb}.qic-event:last-child{border:0}.qic-report-table{width:100%}.qic-report-table td,.qic-report-table th{padding:12px;text-align:left}.qic-page-links{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}.qic-detail-tabs{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}.qic-detail-tabs a{padding:9px 14px;border:1px solid #bfd5dc;border-radius:9px}.qic-detail-tabs a[aria-current=page]{background:#0a5365;color:white}
@media(max-width:600px){.qic-role-intro{padding:16px}.qic-menu-grid{grid-template-columns:1fr}}
</style>
<div id="qic-server-root">
@if(session('qic_status'))<p class="qic-success" role="status">{{ session('qic_status') }}</p>@endif
@if($errors->any())<div class="qic-warning" role="alert"><strong>Data belum tersimpan.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if($showForm)
@include('qic-server.complaint')
@elseif($issue)
@include('qic-server.detail')
@else
@switch($qicPage)
@case('dashboard') @include('qic-server.dashboard') @break
@case('tracking') @include('qic-server.tracking') @break
@case('responses.index') @include('qic-server.responses') @break
@case('notifications') @include('qic-server.activity',['notificationMode'=>true]) @break
@case('audit-log') @include('qic-server.activity',['notificationMode'=>false]) @break
@case('reports') @include('qic-server.reports') @break
@case('profile')
<section class="qic-card"><h2>{{ auth()->user()->name }}</h2><dl class="qic-details"><dt>Email</dt><dd>{{ auth()->user()->email }}</dd><dt>Role akun</dt><dd>{{ auth()->user()->role }}</dd><dt>Workspace</dt><dd>{{ config('qic_ui')[$qicRole]['label'] }}</dd></dl><a class="qic-button" href="{{ route('profile.edit') }}">Pengaturan akun</a></section>
@break
@case('sla-monitoring') @case('ai-insights') @case('master-data') @include('qic-server.future') @break
@case('history')
<div class="qic-role-intro"><h2>Riwayat Penanganan</h2><p>Issue yang telah selesai atau ditolak. Riwayat lengkap dan evidence tersedia pada detail issue.</p></div>
@include('qic-server.issue-list',['listTitle'=>'Arsip Penanganan']) @break
@case('assignments')
<div class="qic-role-intro"><h2>Penugasan Auto Line</h2><p>Complaint sudah diverifikasi dan siap diberikan kepada penanggung jawab proses. Buka detail untuk memilih Auto Line.</p></div>
@include('qic-server.issue-list',['listTitle'=>'Issue Siap Ditugaskan']) @break
@case('reviews.index')
<div class="qic-role-intro"><h2>Antrean Review 3C</h2><p>Tinjau Concern, Cause, Countermeasure, dan evidence versi terbaru sebelum mengambil keputusan.</p></div>
@include('qic-server.review-list') @break
@default @include('qic-server.issue-list')
@endswitch
@endif
</div>
@endsection
