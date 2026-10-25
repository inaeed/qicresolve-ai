<?php

namespace Database\Seeders;

use App\Models\QicIssue;
use App\Models\User;
use App\Services\QicEvidenceStore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class QicVisaSeeder extends Seeder
{
    public function run(): void
    {
        $base = database_path('seed-data/qicresolve');
        $manifestPath = $base.'/manifest.json';
        $manifest = file_get_contents($manifestPath);

        if ($manifest === false) {
            throw new RuntimeException('Manifest impor tidak dapat dibaca: '.$manifestPath);
        }

        $records = json_decode($manifest, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($records) || ! array_is_list($records)) {
            throw new RuntimeException('Manifest impor harus berupa daftar record JSON.');
        }
        // Never reset an existing user's credentials while importing project data.
        $reporter = User::where('email', 'mainassy@qicresolve.test')->where('role', 'main_assy')->first()
            ?? User::where('role', 'main_assy')->first();
        $owner = User::where('role', 'auto_line')->first();
        if (! $reporter || ! $owner) {
            throw new RuntimeException('Akun Main Assy dan Auto Line harus tersedia sebelum impor.');
        }
        $files = new QicEvidenceStore;
        $created = 0;
        try {
            DB::transaction(function () use ($base, $records, $reporter, $owner, $files, &$created) {
                foreach ($records as $row) {
                    $key = 'visa-user-'.pathinfo($row['file'], PATHINFO_FILENAME);
                    if (QicIssue::where('seed_key', $key)->exists()) {
                        continue;
                    }
                    $stem = pathinfo($row['file'], PATHINFO_FILENAME);
                    $has3c = $row['has_3c'];
                    $issue = QicIssue::create([
                        'seed_key' => $key, 'reporter_id' => $reporter->id, 'owner_id' => $has3c ? $owner->id : null,
                        'material' => 'DUMMY-HCSR04-001', 'material_name' => 'Modul Sensor Ultrasonik HC-SR04',
                        'batch' => $row['batch'], 'defect' => $row['defect'], 'quantity' => $row['quantity'], 'priority' => 'Normal',
                        'description' => $row['description'].($has3c ? ' Jumlah 3 dari 50 pcs mengikuti skenario PDF 3C dummy, bukan jumlah unit yang dibuktikan oleh satu foto.' : ''),
                        'occurred_at' => '2026-10-18 08:00:00', 'status' => $has3c ? 'waiting_review' : 'waiting_verification',
                        'source_file' => $row['file'],
                        'source_note' => 'Foto berasal dari paket yang diberikan pengguna sebagai dataset VisA. Subset dan anotasi asli belum diverifikasi. Deskripsi adalah observasi visual untuk pemeriksaan, bukan hasil uji fungsi. Identitas material, batch, jumlah, waktu, dan proses adalah simulasi QICResolve. '.$row['label_note'].($has3c ? ' PDF sumber mempertahankan ID QIC-SIM-001 / 3C-SIM-001 sebagai referensi dokumen; pemetaan ke kode issue aplikasi dicatat di sini.' : ''),
                        'investigation' => $has3c ? 'Hipotesis pada dokumen simulasi: tray belum memiliki sekat pelindung pin dan material dapat bergeser. Belum menjadi penyebab aktual yang terverifikasi.' : null,
                        'containment' => $has3c ? 'Skenario dummy: tahan bin, sortir 50 pcs, pisahkan 3 NG dan 47 OK. Pelepasan material menunggu keputusan Quality.' : null,
                    ]);
                    $issue->update(['code' => 'QI-'.str_pad((string) $issue->id, 3, '0', STR_PAD_LEFT)]);
                    $files->put($issue, $base.'/defects/'.$row['file'], $row['file'], 'image/jpeg', 'defect', $reporter->id);
                    $files->put($issue, $base.'/labels/label-'.$stem.'.png', 'label-'.$stem.'.png', 'image/png', 'label', $reporter->id);
                    $files->put($issue, $base.'/labels/label-'.$stem.'.pdf', 'label-'.$stem.'.pdf', 'application/pdf', 'label', $reporter->id);
                    if ($has3c) {
                        $response = $issue->responses()->create([
                            'author_id' => $owner->id, 'version' => 1,
                            'concern' => 'Skenario PDF: 3 dari 50 pcs memiliki pin bengkok; material dipisahkan sebelum perakitan. Jumlah ini dummy dan bukan anotasi asli foto.',
                            'cause' => 'Hipotesis simulasi: tray belum bersekat dan instruksi belum mencantumkan perlindungan pin. Kesimpulan menunggu verifikasi Quality.',
                            'countermeasure' => 'Tahan bin dan sortir 50 pcs (3 NG/47 OK, dummy). Gunakan tray bersekat dan pelindung pin; perbarui instruksi dan pantau tiga pengiriman berikutnya. Rework/reject memerlukan keputusan Quality.',
                        ]);
                        $files->put($issue, $base.'/3c-original.pdf', '3C_Auto_Line_QICResolve_Simulasi.pdf', 'application/pdf', 'response', $owner->id, $response->id);
                        $files->put($issue, $base.'/label-original.xlsx', 'Label_Material_QICResolve.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'source_label', $reporter->id);
                    }
                    $issue->audits()->create([
                        'actor_id' => $reporter->id, 'action' => 'Impor paket simulasi pengguna', 'from_status' => null,
                        'to_status' => $issue->status, 'note' => 'Foto '.$row['file'].' dan label dipasangkan oleh importer. Status awal berasal dari skenario simulasi, bukan tindakan nyata pengguna. Tidak ada foto sortir atau tray pengganti dalam paket; bukti tersebut tidak dibuat-buat.',
                    ]);
                    $created++;
                }
            });
        } catch (Throwable $e) {
            $files->rollbackFiles();
            throw $e;
        }
        $this->command->info($created.' issue diimpor; data yang pernah diimpor dilewati tanpa ditimpa.');
    }
}
