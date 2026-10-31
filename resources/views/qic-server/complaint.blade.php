<section class="qic-card"><h2>{{ $issue ? 'Lengkapi Complaint' : 'Data Complaint' }}</h2>
<form id="qic-complaint-form" method="POST" action="{{ route('qic.complaints.store') }}" enctype="multipart/form-data">@csrf<input type="hidden" name="workspace" value="{{ $qicRole }}"><input type="hidden" name="code" value="{{ $issue?->code }}">
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
<section style="margin:20px 0;padding:18px;border:1px solid #badde1;border-radius:12px">
    <h3>Bantu Isi dari Evidence</h3>

    <p class="qic-small qic-muted">
        Pilih foto defect dan label material, lalu klik tombol berikut.
        Evidence akan dikirim ke layanan AI untuk dibaca.
        Periksa hasilnya sebelum mengirim complaint.
    </p>

    <button type="button" class="qic-button" id="qic-read-evidence">
        Baca Evidence dengan AI
    </button>

    <p id="qic-extraction-status" role="status" aria-live="polite"></p>

    <p class="qic-small">
        Isian yang sudah terisi tidak akan ditimpa.
        Jumlah reject, waktu kejadian, dan prioritas tetap kamu tentukan.
    </p>
</section>
<div class="qic-actions"><button type="submit" name="submit" value="draft" class="qic-button qic-secondary">Simpan Draft</button><button type="submit" name="submit" value="submit" class="qic-button">Kirim Complaint</button></div>
</form>
<script>
(() => {
    const form = document.getElementById('qic-complaint-form');
    const button = document.getElementById('qic-read-evidence');
    const status = document.getElementById('qic-extraction-status');

    if (!form || !button || button.dataset.aiBound) return;

    button.dataset.aiBound = 'true';

    // Mencegah hasil AI menimpa perubahan pengguna saat proses berlangsung.
    const fields = [
        'material', 'material_name', 'batch', 'defect', 'description'
    ];
    const edited = new Set();

    fields.forEach(name => {
        form.elements.namedItem(name)?.addEventListener('input', () => {
            edited.add(name);
        });
    });

    button.addEventListener('click', async () => {
        const defect = form.elements.namedItem('defect_photo').files[0];
        const label = form.elements.namedItem('label_photo').files[0];

        if (!defect || !label) {
            status.textContent = 'Pilih foto defect dan label material terlebih dahulu.';
            return;
        }

        const data = new FormData();

        data.append('_token', form.elements.namedItem('_token').value);
        data.append('defect_photo', defect);
        data.append('label_photo', label);

        button.disabled = true;
        button.textContent = 'Sedang membaca evidence…';
        status.textContent = 'Mohon tunggu. Form tetap bisa diisi manual.';

        try {
            const response = await fetch(
                @json(route('qic.complaints.extract-evidence')),
                {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: data,
                    credentials: 'same-origin',
                }
            );

            const result = await response.json();

            if (!response.ok) {
                const validationMessage = result.errors
                    ? Object.values(result.errors).flat().join(' ')
                    : null;

                throw new Error(
                    validationMessage
                    || result.message
                    || 'Pembacaan evidence gagal.'
                );
            }

            // Abaikan hasil jika evidence sudah diganti saat AI bekerja.
            if (
                form.elements.namedItem('defect_photo').files[0] !== defect
                || form.elements.namedItem('label_photo').files[0] !== label
            ) {
                status.textContent =
                    'Evidence berubah. Klik Baca Evidence kembali untuk foto terbaru.';
                return;
            }

            let filled = 0;

            fields.forEach(name => {
                const input = form.elements.namedItem(name);
                const value = result.fields?.[name];

                if (
                    input
                    && !input.value.trim()
                    && !edited.has(name)
                    && typeof value === 'string'
                    && value.trim()
                ) {
                    input.value = value.trim();
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    filled++;
                }
            });

            const notes = result.fields?.notes || '';

            status.textContent =
                `${filled} kolom kosong diisi dari usulan AI. `
                + 'Periksa dan koreksi semua isian. '
                + notes;
        } catch (error) {
            status.textContent = error instanceof SyntaxError
                ? 'Server belum mengembalikan hasil yang valid. Coba muat ulang halaman.'
                : error.message;
        } finally {
            button.disabled = false;
            button.textContent = 'Baca Evidence dengan AI';
        }
    });
})();
</script>
</section>
