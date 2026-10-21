<section class="qic-card"><h2>{{ $evidenceTitle ?? 'Evidence' }}</h2>
<div class="qic-server-evidence">
@forelse($attachments as $file)
<article class="qic-server-file">
@if(str_starts_with($file->mime,'image/'))
<a href="{{ route('qic.evidence.show',$file) }}" target="_blank" rel="noopener"><img src="{{ route('qic.evidence.show',$file) }}" alt="{{ $file->original_name }}" loading="lazy" style="width:100%;height:210px;object-fit:contain;background:#f4f8fa;border-radius:8px"></a>
@elseif($file->mime==='application/pdf')
<details><summary>Pratinjau PDF</summary><iframe src="{{ route('qic.evidence.show',$file) }}" title="{{ $file->original_name }}" loading="lazy" style="width:100%;height:420px;border:1px solid #cbd5e1"></iframe></details>
@endif
<strong style="overflow-wrap:anywhere">{{ $file->original_name }}</strong><p class="qic-small qic-muted">{{ $file->category }} · {{ number_format($file->size/1024,1) }} KiB · #{{ $file->id }}</p>
<div class="qic-actions"><a class="qic-button qic-secondary qic-small-button" href="{{ route('qic.evidence.show',$file) }}" target="_blank" rel="noopener">Buka berkas</a><a class="qic-button qic-secondary qic-small-button" href="{{ route('qic.evidence.show',[$file,'download'=>1]) }}">Unduh</a></div>
</article>
@empty<p>Belum ada evidence pada bagian ini.</p>@endforelse
</div></section>
