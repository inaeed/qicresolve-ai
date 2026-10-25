<nav class="qic-detail-tabs" aria-label="Bagian penanganan issue">
<a href="{{ route($qicRole.'.issues.show',['issue'=>$issue->code]) }}" @if($qicPage==='issues.show') aria-current="page" @endif>Detail dan Investigasi</a>
@if($qicRole==='auto-line')<a href="{{ route('auto-line.responses.create',['issue'=>$issue->code]) }}" @if(str_starts_with($qicPage,'responses.')) aria-current="page" @endif>Respons 3C</a><a href="{{ route('auto-line.corrective',['issue'=>$issue->code]) }}" @if($qicPage==='corrective') aria-current="page" @endif>Tindakan Korektif</a>@endif
@if($qicRole==='quality')<a href="{{ route('quality.issues.verify',['issue'=>$issue->code]) }}">Verifikasi</a><a href="{{ route('quality.reviews.show',['issue'=>$issue->code]) }}">Review 3C</a><a href="{{ route('quality.final-verification',['issue'=>$issue->code]) }}">Verifikasi Akhir</a>@endif
</nav>
<section class="qic-card">
@php([$label,$tone]=$statuses[$issue->status])
<div class="qic-status-row"><span class="qic-badge {{ $tone }}">{{ $label }}</span><strong>{{ $issue->code }}</strong></div>
<h2>{{ $issue->material_name ?? $issue->material }}</h2><p style="white-space:pre-wrap">{{ $issue->description }}</p>
<dl class="qic-details">
@foreach(['Material'=>$issue->material,'Batch'=>$issue->batch ?? '—','Jenis defect'=>$issue->defect,'Jumlah reject'=>$issue->quantity.' pcs','Pelapor'=>$issue->reporter->name,'Pemilik proses'=>$issue->owner?->name ?? 'Belum ditugaskan','Kejadian'=>$issue->occurred_at->format('d/m/Y H:i')] as $key=>$value)<dt>{{ $key }}</dt><dd>{{ $value }}</dd>@endforeach
</dl>
@if($issue->source_note)<p class="qic-warning" style="margin-top:20px">{{ $issue->source_note }}</p>@endif
@if($qicRole==='main-assy' && in_array($issue->status,['draft','need_information']))<div class="qic-actions"><a class="qic-button" href="{{ route('main-assy.issues.edit',['issue'=>$issue->code]) }}">Lengkapi Complaint</a></div>@endif
</section>
@include('qic-server.evidence',['attachments'=>$issue->evidence->whereNull('response_id')->whereIn('category',['defect','label','source_label','reference']),'evidenceTitle'=>'Foto Defect dan Label Material'])
@if($issue->investigation)<section class="qic-card"><h2>Investigasi dan Containment</h2><h3>Investigasi</h3><p style="white-space:pre-wrap">{{ $issue->investigation }}</p><h3>Containment</h3><p style="white-space:pre-wrap">{{ $issue->containment }}</p></section>@endif
@if($issue->evidence->where('category','investigation')->count())@include('qic-server.evidence',['attachments'=>$issue->evidence->where('category','investigation'),'evidenceTitle'=>'Evidence Investigasi'])@endif
@foreach($issue->responses as $response)
<section class="qic-card"><h2>Respons 3C · Versi {{ $response->version }}</h2><p class="qic-small qic-muted">{{ $response->author->name }} · {{ $response->created_at->format('d/m/Y H:i') }}</p>
@foreach(['concern'=>'Concern','cause'=>'Cause','countermeasure'=>'Countermeasure'] as $key=>$title)<h3>{{ $title }}</h3><p style="white-space:pre-wrap">{{ $response->$key }}</p>@endforeach
@if($response->decision)<p class="qic-review-note"><strong>Keputusan: {{ $response->decision }}</strong>{{ $response->review_note }}</p>@endif
</section>
@include('qic-server.evidence',['attachments'=>$response->evidence,'evidenceTitle'=>'Dokumen 3C · Versi '.$response->version])
@endforeach
@if($issue->corrective_action)<section class="qic-card"><h2>Hasil Tindakan</h2><p style="white-space:pre-wrap">{{ $issue->corrective_action }}</p><p style="white-space:pre-wrap">{{ $issue->corrective_result }}</p></section>@include('qic-server.evidence',['attachments'=>$issue->evidence->where('category','corrective'),'evidenceTitle'=>'Evidence Tindakan Korektif'])@endif
@include('qic-server.actions')
<section class="qic-card"><h2>Riwayat Issue</h2><ol class="qic-timeline">@foreach($issue->audits as $audit)<li><strong>{{ $audit->action }}</strong><p>{{ $audit->actor->name }} · {{ $audit->created_at->format('d/m/Y H:i') }}</p><p>{{ $audit->note }}</p><span class="qic-small">{{ $statuses[$audit->to_status][0] ?? $audit->to_status }}</span></li>@endforeach</ol></section>
