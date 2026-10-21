# QICResolve - Integrasi Database dan Evidence VisA

## Hasil
- 11 issue, satu per foto 000.JPG sampai 010.JPG.
- 35 evidence: 11 foto asli, 11 PNG label, 11 PDF label, satu PDF 3C asli, satu workbook label asli.
- Satu respons 3C versi 1 pada foto 001.JPG, berstatus Menunggu Review 3C. Sepuluh issue lainnya Menunggu Verifikasi.
- Daftar bersama dari database, dengan pemeriksaan pelapor dan penanggung jawab sesuai akun.
- Semua issue yang diizinkan role ditampilkan, terbaru diperbarui berada di atas.
- Complaint baru, evidence, penugasan, investigasi/containment, versi 3C, review, tindakan korektif dan closure memakai server.
- File asli disimpan privat di storage/app/private/qicresolve. Tabel qic_evidence menyimpan path, kategori, ukuran, MIME dan SHA-256.
- Pratinjau foto dan PDF tersedia pada detail issue; Excel diunduh lewat tombol Unduh.

## Pemasangan pada proyek yang berjalan
1. Cadangkan kode dan database aktif milikmu.
2. Ekstrak ZIP ini. Salin folder app, config, database, resources, routes, dan tests ke root proyek Laravel; gabungkan folder dan ganti file yang sama. ZIP hanya berisi file yang diperlukan, bukan pengganti keseluruhan proyek.
3. Gunakan .env milik proyekmu. Jangan menggantinya. Database SQLite kosong dari ZIP sumber tidak disertakan dan tidak menimpa database pengguna.
4. Pastikan akun dengan role main_assy dan auto_line tersedia di database aktif; quality dan project_owner dipakai untuk review dan pemeriksaan. Importer tidak membuat atau mengganti password.
5. Buka terminal di root proyek dan jalankan berurutan:

```bash
composer dump-autoload
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=QicVisaSeeder
php artisan qic:evidence-check
npm run build
```

Gunakan `migrate`, bukan `migrate:fresh`, agar tabel akun lama tidak dihapus. Tidak perlu `storage:link`; evidence diakses melalui route yang memeriksa login dan kepemilikan.
6. Refresh Ctrl + Shift + R. Login menggunakan akun yang biasa dipakai.
7. Halaman workspace menampilkan banner Database aktif. Isi sessionStorage/IndexedDB lama tidak dimasukkan atau digunakan oleh halaman server.

Jika basis datamu sudah memiliki issue server lain, angka total dapat lebih dari 11. Import ulang melewati data yang sudah ada berdasarkan seed_key, tidak menghapus atau menimpa perubahannya.

## Pemeriksaan UI
- Main Assy: dashboard menunjukkan 11 issue impor jika pelapor sama dengan akun Main Assy yang digunakan saat impor. Buka satu issue: ada foto, PNG label, PDF label.
- Foto 001.JPG: detail memuat PDF 3C simulasi dan workbook label sumber. Identitas dokumen QIC-SIM-001/3C-SIM-001 dicatat sebagai referensi, tidak dipalsukan menjadi nomor baru.
- Quality: seluruh issue non-draft terlihat. Verifikasi salah satu issue lain, lalu pilih penanggung jawab Auto Line.
- Auto Line: hanya issue yang ditugaskan kepadanya. Saat awal impor hanya satu issue yang ditugaskan (001.JPG). Ini sesuai hak akses, bukan data hilang.
- Project Owner: boleh membuka ketiga workspace dan seluruh issue untuk memeriksa data.
- Logout/login atau perangkat lain tetap membaca database dan berkas yang sama, selama mengakses server yang sama.
- Tombol Hapus Semua Issue demo tidak disediakan untuk database server. Paket ini tidak menghapus data server secara massal.

## Alur baru
Main Assy kirim complaint dengan foto/label -> Quality verifikasi -> Quality tugaskan akun Auto Line -> Auto Line terima -> simpan investigasi dan containment -> kirim 3C -> Quality setujui/minta revisi/tolak -> Auto Line unggah hasil dan bukti tindakan -> Quality verifikasi dan tutup.
Setiap kirim ulang 3C membuat versi baru. Catatan Quality, berkas dan versi lama tetap tersimpan. Closure memerlukan hasil dan evidence tindakan.

## Kejujuran data
- Foto berasal dari paket pengguna yang disebut sebagai VisA. Paket tidak memuat anotasi dan metadata subset resmi untuk mengonfirmasi asal tiap foto.
- Deskripsi ditulis berdasarkan observasi gambar, bukan diagnosis atau uji fungsi.
- Identitas material, batch/bin, jumlah reject, waktu dan skenario proses merupakan SIMULASI.
- Lima batch unik 001-005 ditemukan pada workbook yang dikirim, dengan salinan label kiri/kanan. Label tambahan 006-011 dibuat mengikuti template kosong, ditandai di source_note.
- PDF 3C asli hanya ditautkan ke foto 001.JPG sebagai satu skenario. Bukti sortir, foto tray pengganti, hasil pengukuran dan bukti penutupan tidak ada dalam paket; tidak dibuat atau dinyatakan tersedia.
- Jumlah 3 dari 50 pada skenario 3C adalah dummy; satu foto tidak membuktikan tiga unit.

## Batas tahap ini
Halaman inti workspace memakai Blade server agar database dan evidence benar-benar terhubung. Sidebar serta gaya umum dipertahankan. Fitur SLA/AI dan master lanjutan diberi keterangan belum aktif, bukan hasil hitung/analisis palsu. Riwayat dan notifikasi pada tahap ini berupa daftar issue/tindakan dari database, belum push notification atau penanda sudah dibaca. Laporan menampilkan daftar, belum ekspor database ke Excel.

## Verifikasi teknis
Pengujian lokal: PHP 8.3, Laravel dari composer.lock pengguna, SQLite testing terisolasi. 7 pengujian / 141 assertions lulus: persistence berkas, akses role, tahapan workflow, impor idempotent, versi 3C, render seluruh halaman dan closure lengkap. Lint PHP 51 file juga lulus.
File test ada di tests/Feature/QicEvidenceDatabaseTest.php. Jalankan dengan database testing terisolasi (SQLite :memory:) memakai konfigurasi test proyek, bukan database berisi data pengguna:

```bash
php artisan test --filter=QicEvidenceDatabaseTest
```

`qic:evidence-check` memeriksa file dan SHA-256 tanpa menghapus apa pun. Pada impor bersih hasilnya 11 issue | 35 evidence | 0 masalah.

Batas aplikasi: 5 MiB per berkas, maksimal 10 lampiran per tindakan. Jika PHP menolak unggahan sebelum validasi Laravel, sesuaikan upload_max_filesize dan post_max_size pada PHP server (misalnya 6M dan 64M), lalu restart server.
