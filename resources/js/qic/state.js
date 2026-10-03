// Pure demo domain: tidak mengakses API, mengunggah berkas, atau mengubah akun Laravel.
export const SCHEMA = 4;
export const STATUS = {
    draft: 'Draft', waiting_verification: 'Menunggu Verifikasi', need_information: 'Perlu Informasi',
    rejected: 'Issue Ditolak', verified: 'Terverifikasi', assigned: 'Ditugaskan',
    investigation: 'Investigasi / Containment', three_c_progress: '3C Dalam Proses',
    waiting_review: 'Menunggu Review 3C', revision_requested: 'Revisi 3C Diminta',
    three_c_rejected: '3C Ditolak', approved: '3C Disetujui', corrective: 'Tindakan Korektif',
    waiting_final: 'Menunggu Verifikasi Akhir', closed: 'Selesai', cancelled: 'Dibatalkan',
};
export const ROLE = { 'main-assy': 'Main Assy', 'auto-line': 'Auto Line', quality: 'Quality' };
export const TERMINAL = ['closed', 'cancelled', 'rejected'];
export const MAX_FILE_BYTES = 5 * 1024 * 1024;
export function fileError(file) {
    if (!file) return 'Pilih berkas terlebih dahulu.';
    if (!['image/jpeg', 'image/png', 'image/webp', 'application/pdf'].includes(file.type)) return 'Gunakan JPEG, PNG, WebP, atau PDF.';
    if (file.size <= 0 || file.size > MAX_FILE_BYTES) return 'Ukuran berkas harus lebih dari 0 dan maksimal 5 MiB.';
    return '';
}
export function photoError(file) {
    if (file && !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return 'Foto harus berformat JPEG, PNG, atau WebP.';
    return fileError(file);
}
const fixedDate = '2026-10-01T08:00:00+07:00';
function sampleVersion(n = 1, decision = null) {
    return {
        version: n, concern: 'Goresan pada permukaan housing mengganggu kesesuaian visual produk.',
        cause: 'Simulasi investigasi menunjukkan kontak antarkomponen saat dipindahkan dalam tray.',
        countermeasure: 'Gunakan separator tray dan verifikasi ulang kondisi permukaan sebelum dipindahkan.',
        evidence: ['pemeriksaan-tray-simulasi.pdf'], submittedAt: fixedDate,
        review: decision ? { decision, reason: 'Rencana perbaikan dan evidence simulasi sudah sesuai.', at: fixedDate } : null,
    };
}
export function seedState() {
    const cases = [
        ['QI-001', 'Housing A', 'waiting_verification', null, 'Tinggi', 27],
        ['QI-002', 'Bracket B', 'need_information', null, 'Normal', 40],
        ['QI-003', 'Cover C', 'assigned', 'Auto Line 1', 'Tinggi', 49],
        ['QI-004', 'Housing D', 'waiting_review', 'Auto Line 1', 'Normal', 12],
        ['QI-005', 'Connector E', 'approved', 'Auto Line 2', 'Normal', 30],
        ['QI-006', 'Housing F', 'waiting_final', 'Auto Line 2', 'Normal', 36],
        ['QI-007', 'Cover G', 'closed', 'Auto Line 1', 'Normal', 20],
    ];
    return {
        schema: SCHEMA, counter: 8, profiles: {}, read: [], notices: [],
        master: [
            { id: 'M-01', category: 'Material', name: 'Housing A', active: true },
            { id: 'M-02', category: 'Defect', name: 'Goresan', active: true },
            { id: 'M-03', category: 'Process Owner', name: 'Auto Line 1', active: true },
            { id: 'M-04', category: 'Process Owner', name: 'Auto Line 2', active: true },
        ],
        issues: cases.map(([id, material, status, owner, priority, elapsed]) => ({
            id, material, status, owner, priority, quantity: 5, defect: 'Goresan',
            description: `Temuan simulasi pada ${material}; periksa kondisi permukaan dan identitas material.`,
            occurredAt: '2026-10-01T08:00', createdAt: fixedDate, updatedAt: fixedDate,
            reporter: 'Main Assy Leader', photos: { defect: 'defect-simulasi.jpg', label: 'label-simulasi.jpg' },
            investigation: owner ? 'Contoh: pemeriksaan visual pada sampel dan peninjauan tray material.' : '',
            containment: owner ? 'Contoh: pisahkan material dan lakukan pemeriksaan ulang.' : '',
            investigationEvidence: owner ? ['hasil-pemeriksaan-simulasi.pdf'] : [],
            versions: ['waiting_review', 'approved', 'waiting_final', 'closed'].includes(status)
                ? [sampleVersion(1, status === 'waiting_review' ? null : 'approve')] : [],
            responseDraft: null,
            corrective: ['waiting_final', 'closed'].includes(status) ? {
                action: 'Separator tray diterapkan dan material diperiksa ulang (simulasi).',
                result: 'Contoh hasil: sampel yang diperiksa memenuhi kriteria visual.',
                evidence: ['bukti-tindakan-simulasi.jpg'], submittedAt: fixedDate,
            } : null,
            sla: { stage: 'Pengajuan 3C pertama', target: 48, elapsed, snapshot: 'Snapshot data contoh, bukan penghitungan real-time' },
            audit: [{ id: `${id}-seed`, at: fixedDate, actor: 'Dataset simulasi', action: 'Memuat contoh kasus', note: `Status awal: ${STATUS[status]}`, to: status }],
        })),
    };
}
const rules = {
    verify: ['quality', ['waiting_verification'], 'verified'],
    return_info: ['quality', ['waiting_verification'], 'need_information'],
    reject_issue: ['quality', ['waiting_verification'], 'rejected'],
    assign: ['quality', ['verified'], 'assigned'],
    acknowledge: ['auto-line', ['assigned'], 'investigation'],
    save_investigation: ['auto-line', ['investigation', 'three_c_progress', 'revision_requested', 'three_c_rejected'], null],
    save_response: ['auto-line', ['investigation', 'three_c_progress', 'revision_requested', 'three_c_rejected'], 'three_c_progress'],
    submit_response: ['auto-line', ['investigation', 'three_c_progress', 'revision_requested', 'three_c_rejected'], 'waiting_review'],
    approve: ['quality', ['waiting_review'], 'approved'],
    revise: ['quality', ['waiting_review'], 'revision_requested'],
    reject_response: ['quality', ['waiting_review'], 'three_c_rejected'],
    save_corrective: ['auto-line', ['approved', 'corrective'], 'corrective'],
    submit_corrective: ['auto-line', ['approved', 'corrective'], 'waiting_final'],
    return_corrective: ['quality', ['waiting_final'], 'corrective'],
    close: ['quality', ['waiting_final'], 'closed'],
    edit: ['main-assy', ['draft', 'need_information'], null],
    submit: ['main-assy', ['draft', 'need_information'], 'waiting_verification'],
};
export const ACTION = {
    verify: 'Verifikasi issue', return_info: 'Minta kelengkapan informasi', reject_issue: 'Tolak issue', assign: 'Tugaskan process owner',
    acknowledge: 'Terima penugasan', save_investigation: 'Simpan investigasi', save_response: 'Simpan draft 3C', submit_response: 'Kirim respons 3C',
    approve: 'Setujui 3C', revise: 'Minta revisi 3C', reject_response: 'Tolak respons 3C', save_corrective: 'Simpan tindakan korektif',
    submit_corrective: 'Kirim hasil tindakan', return_corrective: 'Minta perbaikan tindakan', close: 'Tutup issue', edit: 'Simpan complaint', submit: 'Kirim complaint', cancel: 'Batalkan issue',
};
export function canAct(issue, role, action) {
    if (action === 'cancel') return role === 'quality' && !TERMINAL.includes(issue.status) && issue.status !== 'draft';
    const rule = rules[action];
    return Boolean(rule && role === rule[0] && rule[1].includes(issue.status));
}
function requireText(value, message) { if (!String(value ?? '').trim()) throw new Error(message); }
export function validateComplaint(data, submit = true) {
    requireText(data.material, 'Material wajib diisi.');
    if (!submit) return;
    requireText(data.defect, 'Jenis defect wajib diisi.');
    requireText(data.description, 'Deskripsi temuan wajib diisi.');
    if (!Number.isInteger(Number(data.quantity)) || Number(data.quantity) < 1) throw new Error('Jumlah reject harus bilangan bulat positif.');
    if (!data.occurredAt || Number.isNaN(Date.parse(data.occurredAt))) throw new Error('Waktu kejadian wajib valid.');
    if (!data.photos?.defect || !data.photos?.label) throw new Error('Foto defect dan label material wajib dipilih.');
}
function log(issue, role, action, note, from) {
    const at = new Date().toISOString();
    issue.updatedAt = at;
    issue.audit.push({ id: `${issue.id}-${issue.audit.length}-${at}`, at, actor: ROLE[role], action: ACTION[action] ?? action, note: note || '', from, to: issue.status });
}
export function transition(issue, role, action, payload = {}) {
    if (!canAct(issue, role, action)) throw new Error('Tindakan tidak tersedia untuk peran dan status saat ini.');
    const next = structuredClone(issue);
    const previous = issue.status;
    const reasonActions = ['verify', 'return_info', 'reject_issue', 'approve', 'revise', 'reject_response', 'close', 'return_corrective', 'cancel'];
    if (reasonActions.includes(action)) requireText(payload.reason, 'Alasan keputusan Quality wajib diisi.');
    if (action === 'assign') { requireText(payload.owner, 'Pilih Auto Line tujuan.'); next.owner = payload.owner; }
    if (action === 'edit' || action === 'submit') {
        validateComplaint(payload, action === 'submit');
        for (const key of ['material', 'defect', 'description', 'quantity', 'occurredAt', 'priority', 'photos']) next[key] = structuredClone(payload[key] ?? next[key]);
    }
    if (action === 'save_investigation') {
        requireText(payload.investigation, 'Hasil investigasi wajib diisi.');
        next.investigation = payload.investigation; next.containment = payload.containment || '';
        next.investigationEvidence = payload.evidence || [];
    }
    if (action === 'save_response' || action === 'submit_response') {
        const response = { concern: payload.concern || '', cause: payload.cause || '', countermeasure: payload.countermeasure || '', evidence: payload.evidence || [] };
        if (action === 'submit_response') {
            for (const key of ['concern', 'cause', 'countermeasure']) requireText(response[key], 'Concern, Cause, dan Countermeasure wajib diisi.');
            if (!response.evidence.length) throw new Error('Tambahkan evidence pendukung 3C.');
            next.versions.push({ ...response, version: next.versions.length + 1, submittedAt: new Date().toISOString(), review: null });
            next.responseDraft = null;
        } else next.responseDraft = response;
    }
    if (['approve', 'revise', 'reject_response'].includes(action)) {
        if (!next.versions.length) throw new Error('Tidak ada versi 3C untuk ditinjau.');
        next.versions.at(-1).review = { decision: action, reason: payload.reason, at: new Date().toISOString() };
    }
    if (['save_corrective', 'submit_corrective'].includes(action)) {
        requireText(payload.action, 'Isi pelaksanaan tindakan korektif.');
        if (action === 'submit_corrective') {
            requireText(payload.result, 'Isi hasil pemeriksaan tindakan.');
            if (!payload.evidence?.length) throw new Error('Tambahkan bukti pelaksanaan tindakan.');
        }
        next.corrective = { action: payload.action, result: payload.result || '', evidence: payload.evidence || [], submittedAt: new Date().toISOString() };
    }
    if (action === 'close' && (!next.corrective?.evidence?.length || !next.corrective.result)) throw new Error('Tindakan, hasil, dan evidence harus lengkap sebelum penutupan.');
    next.status = action === 'cancel' ? 'cancelled' : (rules[action][2] ?? next.status);
    log(next, role, action, payload.reason || payload.note || '', previous);
    return next;
}
export function createIssue(state, data, submit) {
    validateComplaint(data, submit);
    const id = `QI-${String(state.counter).padStart(3, '0')}`;
    const now = new Date().toISOString();
    const issue = {
        id, ...data, status: submit ? 'waiting_verification' : 'draft', createdAt: now, updatedAt: now,
        owner: null, reporter: 'Main Assy Leader', versions: [], responseDraft: null,
        investigation: '', containment: '', investigationEvidence: [], corrective: null, sla: null, audit: [],
    };
    log(issue, 'main-assy', submit ? 'submit' : 'edit', 'Dibuat pada demonstrasi frontend.', null);
    return issue;
}
export function scopedIssues(state, role) {
    if (role === 'quality') return state.issues.filter(i => i.status !== 'draft');
    if (role === 'auto-line') return state.issues.filter(i => i.owner && !['draft', 'rejected'].includes(i.status));
    return state.issues;
}
export function createStore(storage, userId) {
    const key = `qicresolve-demo-v${SCHEMA}-${userId}`;
    let state = seedState(); let warning = '';
    try {
        const raw = storage?.getItem(key);
        if (raw) {
            const value = JSON.parse(raw);
            if (value.schema === SCHEMA && value.profiles && typeof value.profiles === 'object' && Array.isArray(value.issues) && Array.isArray(value.master) && Array.isArray(value.read) && Number.isInteger(value.counter) && value.counter > 0 && value.issues.every(i => STATUS[i.status] && /^QI-\d+$/.test(i.id) && Array.isArray(i.audit) && Array.isArray(i.versions))) state = value;
            else warning = 'Data demo lama tidak cocok; dataset contoh dimuat ulang.';
        }
        if (!storage) warning = 'Penyimpanan tab tidak tersedia; perubahan hanya berlaku selama halaman ini dibuka.';
    } catch { warning = 'Data demo tidak dapat dibaca; dataset contoh dimuat. Periksa izin penyimpanan browser.'; }
    return {
        get: () => state, warning: () => warning,
        save(next) {
            try { if (!storage) throw new Error('No storage'); storage.setItem(key, JSON.stringify(next)); }
            catch { warning = 'Perubahan hanya di memori halaman ini; penyimpanan tab gagal. Berpindah halaman dapat menghilangkan perubahan.'; }
            state = next;
        },
        reset() { const next = seedState(); this.save(next); },
    };
}
