<section class="qic-card">
    <h2>Quality AI Insight</h2>

    <p class="qic-small qic-muted">
        Tanya tentang issue, tren defect, dan SLA simulasi.
        Periode menyaring tanggal pembuatan issue.
        Evaluasi SLA menggunakan aturan saat ini dan riwayat audit.
    </p>

    <form
        id="qic-insight-form"
        action="{{ route('qic.quality.insight') }}"
        method="POST"
    >
        @csrf

        <label class="qic-field">
            Dari tanggal
            <input
                type="date"
                name="from"
                value="{{ now('Asia/Jakarta')->startOfMonth()->toDateString() }}"
                required
            >
        </label>

        <label class="qic-field">
            Sampai tanggal
            <input
                type="date"
                name="to"
                value="{{ now('Asia/Jakarta')->toDateString() }}"
                required
            >
        </label>

        <label class="qic-field">
            Pertanyaan
            <textarea
                name="question"
                maxlength="1000"
                required
                placeholder="Contoh: Issue mana yang memiliki pelanggaran SLA dan perlu ditindaklanjuti?"
            ></textarea>
        </label>

        <button type="submit" class="qic-button">
            Tanya AI
        </button>
    </form>

    <p id="qic-insight-status" role="status" aria-live="polite"></p>
</section>

<section class="qic-card" id="qic-insight-result" hidden>
    <h2>Jawaban Assistant</h2>
    <p id="qic-insight-answer" style="white-space:pre-wrap"></p>
    <nav id="qic-insight-references" aria-label="Issue referensi"></nav>
</section>

<script>
(() => {
    const form = document.getElementById('qic-insight-form');
    if (!form || form.dataset.bound) return;
    form.dataset.bound = 'true';

    const button = form.querySelector('button[type="submit"]');
    const status = document.getElementById('qic-insight-status');
    const result = document.getElementById('qic-insight-result');
    const answer = document.getElementById('qic-insight-answer');
    const references = document.getElementById('qic-insight-references');

    form.addEventListener('submit', async event => {
        event.preventDefault();

        button.disabled = true;
        result.hidden = true;
        references.replaceChildren();
        status.textContent = 'Sedang membaca data dan menyusun jawaban…';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.elements.namedItem('_token').value,
                },
                body: new FormData(form),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    Object.values(data.errors || {}).flat()[0]
                    || data.message
                    || 'Permintaan belum berhasil.'
                );
            }

            // Gunakan teks, bukan HTML dari AI.
            answer.textContent = data.answer;

            for (const reference of data.references || []) {
                const link = document.createElement('a');
                link.textContent = reference.code;
                link.href = reference.url;
                link.className = 'qic-button';
                link.style.margin = '4px';
                references.append(link);
            }

            result.hidden = false;
            status.textContent =
                `Jawaban berdasarkan ${data.issue_count} issue `
                + 'pada periode yang dipilih. '
                + 'Rekomendasi perlu ditinjau Quality.';
        } catch (error) {
            status.textContent = error instanceof SyntaxError
                ? 'Respons server tidak valid. Muat ulang halaman.'
                : error.message;
        } finally {
            button.disabled = false;
        }
    });
})();
</script>