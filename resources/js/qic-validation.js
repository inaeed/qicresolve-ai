export const MAX_PHOTO_BYTES = 5 * 1024 * 1024;
export function photoError(file) {
    if (!file) return 'Pilih foto terlebih dahulu.';
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return 'Gunakan foto JPEG, PNG, atau WebP.';
    if (file.size <= 0) return 'Berkas kosong tidak dapat digunakan.';
    if (file.size > MAX_PHOTO_BYTES) return 'Ukuran foto maksimal 5 MB.';
    return '';
}
