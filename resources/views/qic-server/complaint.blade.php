<section class="qic-card"><h2>{{ $issue ? 'Lengkapi Complaint' : 'Data Complaint' }}</h2>
<form method="POST" action="{{ route('qic.complaints.store') }}" enctype="multipart/form-data">
@csrf<input type="hidden" name="workspace" value="{{ $qicRole }}"><input type="hidden" name="code" value="{{ $issue?->code }}">
<div class="qic-form-grid">
@foreach(['material'=>'Material / Part Number','material_name'=>'Nama material','batch'=>'Batch / Lot','defect'=>'Jenis defect (ketik manual)'] as $name=>$label)
<label class="qic-field">{{ $label }}<input name="{{ $name }}" value="{{ old($name,$issue?->$name) }}" maxlength="200" @required(in_array($name,['material','defect']))></label>
@endforeach
<label class="qic-field">Waktu kejadian<input type="datetime-local" name="occurred_at" value="{{ old('occurred_at',$issue?->occurred_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" required></label>
<label class="qic-field">Jumlah reject<input type="number" name="quantity" min="1" max="1000000" value="{{ old('quantity',$issue?->quantity ?? 1) }}" required></label>
<label class="qic-field">Prioritas<select name="priority">@foreach(['Normal','Tinggi'] as $p)<option @selected(old('priority',$issue?->priority)===$p)>{{ $p }}</option>@endforeach</select></label>
<label class="qic-field qic-wide">Deskripsi temuan<textarea name="description" maxlength="5000" required>{{ old('description',$issue?->description) }}</textarea></label>
<label class="qic-field">Foto defect<input type="file" name="defect_photo" accept="image/jpeg,image/png,image/webp"><small>Maksimal 5 MiB. Wajib ketika dikirim; berkas lama tetap ada saat mengedit.</small></label>
<label class="qic-field">Foto / PDF label material<input type="file" name="label_photo" accept="image/jpeg,image/png,image/webp,application/pdf"><small>Maksimal 5 MiB. Label simulasi diperbolehkan.</small></label>
</div>
@if($issue)<p class="qic-small">Mengunggah bukti baru akan menambah evidence, tanpa menghapus bukti sebelumnya.</p>@endif
<div class="qic-actions"><button type="submit" name="submit" value="draft" class="qic-button qic-secondary">Simpan Draft</button><button type="submit" name="submit" value="submit" class="qic-button">Kirim Complaint</button></div>
</form></section>
