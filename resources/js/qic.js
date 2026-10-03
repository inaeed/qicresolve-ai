import { photoError } from './qic-validation.js';

function initQic() {
    const root = document.querySelector('.qic-app');
    if (!root || root.dataset.initialized) return;
    root.dataset.initialized = 'true';
    root.querySelector('[data-menu]')?.addEventListener('click', (event) => {
        const open = root.querySelector('#qic-sidebar').classList.toggle('is-open');
        event.currentTarget.setAttribute('aria-expanded', String(open));
    });
    const list = root.querySelector('[data-issue-list]');
    if (list) {
        const search = list.querySelector('[data-search-input]');
        const status = list.querySelector('[data-status-input]');
        const filter = () => {
            let visible = 0;
            list.querySelectorAll('[data-issue-row]').forEach(row => {
                const match = row.dataset.search.includes(search.value.trim().toLowerCase()) && (!status.value || row.dataset.status === status.value);
                row.hidden = !match;
                if (match) visible++;
            });
            list.querySelector('[data-empty]').hidden = visible > 0;
        };
        search.addEventListener('input', filter);
        status.addEventListener('change', filter);
    }
    const form = root.querySelector('[data-issue-form]');
    if (!form) return;
    const dialog = root.querySelector('#evidence-dialog');
    const keys = ['defect', 'label'];
    let committed = { defect: null, label: null };
    let draft = { ...committed };
    const urls = {};
    const preview = (key, file) => {
        if (urls[key]) URL.revokeObjectURL(urls[key]);
        delete urls[key];
        const image = dialog.querySelector(`[data-photo-preview="${key}"]`);
        image.hidden = !file;
        image.removeAttribute('src');
        if (file) {
            urls[key] = URL.createObjectURL(file);
            image.src = urls[key];
        }
    };
    form.addEventListener('submit', (event) => {
        event.preventDefault(); // Prototipe: jangan mengirim data/foto ke endpoint yang belum tersedia.
        if (!form.reportValidity()) return;
        const message = form.querySelector('[data-form-feedback]');
        message.hidden = false;
        message.textContent = keys.every(key => committed[key])
            ? 'Validasi demo berhasil. Data dan foto belum dikirim atau disimpan ke server. Integrasi backend diperlukan untuk membuat complaint.'
            : 'Lengkapi foto defect dan foto label material melalui Pilih Foto Bukti.';
        message.focus();
    });
    form.querySelector('[data-open-evidence]').addEventListener('click', () => {
        draft = { ...committed };
        keys.forEach(key => {
            dialog.querySelector(`[data-photo="${key}"]`).value = '';
            dialog.querySelector(`[data-photo-error="${key}"]`).textContent = '';
            preview(key, draft[key]);
        });
        dialog.querySelector('[data-modal-error]').textContent = '';
        dialog.showModal();
    });
    dialog.querySelectorAll('[data-close-evidence]').forEach(button => button.addEventListener('click', () => dialog.close()));
    keys.forEach(key => {
        const input = dialog.querySelector(`[data-photo="${key}"]`);
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            const error = photoError(file);
            dialog.querySelector(`[data-photo-error="${key}"]`).textContent = error;
            if (error) {
                input.value = '';
                draft[key] = null;
                preview(key, null);
                return;
            }
            draft[key] = file;
            preview(key, file);
        });
    });
    dialog.querySelector('[data-attach-evidence]').addEventListener('click', () => {
        if (!keys.every(key => draft[key])) {
            dialog.querySelector('[data-modal-error]').textContent = 'Pilih kedua foto sebelum melampirkan bukti.';
            return;
        }
        committed = { ...draft };
        form.querySelector('[data-evidence-summary]').textContent = `Foto defect: ${committed.defect.name} · Label: ${committed.label.name}`;
        form.querySelector('[data-form-feedback]').hidden = true;
        dialog.close();
    });
    dialog.addEventListener('close', () => keys.forEach(key => preview(key, null)));
    // Tetap nonaktif bila skrip gagal dimuat, agar form tidak mengirim GET tanpa sengaja.
    form.querySelector('[data-form-fields]').disabled = false;
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initQic);
else initQic();
document.addEventListener('livewire:navigated', initQic);
