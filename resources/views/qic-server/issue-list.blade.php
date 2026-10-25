<section class="qic-card">
<h2>{{ $listTitle ?? $qicTitle }}</h2>
<p class="qic-small qic-muted">{{ $records->count() }} issue · Yang terakhir diperbarui tampil paling atas.</p>
<form method="GET" class="qic-filters">
<label class="qic-field">Cari issue<input name="search" value="{{ request('search') }}" placeholder="ID, material, atau deskripsi"></label>
<label class="qic-field">Status<select name="status"><option value="">Semua status</option>@foreach($statuses as $key=>[$label,$tone])<option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>@endforeach</select></label>
<div class="qic-actions"><button class="qic-button" type="submit">Tampilkan</button>@if($qicRole==='main-assy')<a class="qic-button qic-secondary" href="{{ route('main-assy.issues.create') }}">+ Buat Complaint</a>@endif</div>
</form>
<div class="qic-table-scroll"><table><thead><tr><th>ID Issue</th><th>Material / Defect</th><th>Status</th><th>Pemilik proses</th><th>Diperbarui</th><th>Tindakan</th></tr></thead><tbody>
@forelse($records as $row)
<tr><td><a href="{{ route($qicRole.'.issues.show',['issue'=>$row->code]) }}">{{ $row->code }}</a></td><td>{{ $row->material }}@if($row->source_file)<br><small>Foto: {{ $row->source_file }}</small>@endif<br><span class="qic-small qic-muted">{{ $row->defect }}</span></td><td>@php([$label,$tone]=$statuses[$row->status])<span class="qic-badge {{ $tone }}">{{ $label }}</span></td><td>{{ $row->owner?->name ?? 'Belum ditugaskan' }}</td><td>{{ $row->updated_at->format('d/m/Y H:i') }}</td><td><a href="{{ route($qicRole.'.issues.show',['issue'=>$row->code]) }}">Buka detail →</a></td></tr>
@empty<tr><td colspan="6">Belum ada issue yang sesuai filter dan hak akses akun ini.</td></tr>@endforelse
</tbody></table></div>
</section>