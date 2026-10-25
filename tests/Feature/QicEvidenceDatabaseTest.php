<?php

namespace Tests\Feature;

use App\Models\QicEvidence;
use App\Models\QicIssue;
use App\Models\User;
use Database\Seeders\QicVisaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QicEvidenceDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function userFor(string $role): User
    {
        return User::forceCreate(['name' => $role, 'email' => uniqid().'@example.test', 'role' => $role, 'password' => bcrypt('test-password'), 'email_verified_at' => now()]);
    }

    private function issue(User $reporter, ?User $owner = null, string $status = 'waiting_verification'): QicIssue
    {
        $issue = QicIssue::create(['reporter_id' => $reporter->id, 'owner_id' => $owner?->id, 'material' => 'DUMMY-HCSR04-001', 'defect' => 'Pin bengkok', 'quantity' => 1, 'description' => 'Simulasi pengujian', 'occurred_at' => now(), 'status' => $status]);
        $issue->update(['code' => 'QI-'.str_pad((string) $issue->id, 3, '0', STR_PAD_LEFT)]);

        return $issue;
    }

    public function test_complaint_and_files_persist_in_database(): void
    {
        Storage::fake('local');
        $reporter = $this->userFor('main_assy');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j2b0AAAAASUVORK5CYII=');
        $this->actingAs($reporter)->post(route('qic.complaints.store'), [
            'workspace' => 'main-assy', 'material' => 'DUMMY-001', 'defect' => 'Pin bengkok', 'quantity' => 1, 'priority' => 'Normal', 'description' => 'Observasi visual simulasi', 'occurred_at' => now()->subMinute()->format('Y-m-d\TH:i'), 'submit' => 'submit',
            'defect_photo' => UploadedFile::fake()->createWithContent('defect.png', $png),
            'label_photo' => UploadedFile::fake()->createWithContent('label.png', $png),
        ])->assertRedirect();
        $this->assertDatabaseCount('qic_issues', 1);
        $this->assertDatabaseCount('qic_evidence', 2);
        foreach (QicEvidence::all() as $file) {
            Storage::disk('local')->assertExists($file->path);
        }
        $this->actingAs($this->userFor('quality'))->get(route('quality.issues.index'))->assertOk()->assertSee('DUMMY-001');
    }

    public function test_evidence_access_checks_reporter_and_assigned_owner(): void
    {
        Storage::fake('local');
        $reporter = $this->userFor('main_assy');
        $owner = $this->userFor('auto_line');
        $issue = $this->issue($reporter, $owner, 'assigned');
        Storage::disk('local')->put('qicresolve/test.pdf', 'test file');
        $file = $issue->evidence()->create(['uploaded_by' => $reporter->id, 'category' => 'response', 'original_name' => 'test.pdf', 'path' => 'qicresolve/test.pdf', 'mime' => 'application/pdf', 'size' => 9, 'sha256' => hash('sha256', 'test file')]);
        $this->get(route('qic.evidence.show', $file))->assertRedirect();
        $this->actingAs($this->userFor('main_assy'))->get(route('qic.evidence.show', $file))->assertNotFound();
        $this->actingAs($this->userFor('auto_line'))->get(route('qic.evidence.show', $file))->assertNotFound();
        $this->actingAs($owner)->get(route('qic.evidence.show', $file))->assertOk();
        $this->actingAs($reporter)->get(route('qic.evidence.show', $file))->assertOk();
        $this->actingAs($this->userFor('quality'))->get(route('qic.evidence.show', $file))->assertOk();
    }

    public function test_main_assy_cannot_approve_and_quality_cannot_skip_review_stage(): void
    {
        $reporter = $this->userFor('main_assy');
        $issue = $this->issue($reporter);
        $this->actingAs($reporter)->post(route('qic.issues.action', $issue->code), ['workspace' => 'main-assy', 'action' => 'approve', 'note' => 'Tidak boleh'])->assertForbidden();
        $this->actingAs($this->userFor('quality'))->post(route('qic.issues.action', $issue->code), ['workspace' => 'quality', 'action' => 'approve', 'note' => 'Tidak boleh melompati tahap'])->assertSessionHasErrors('action');
        $this->assertSame('waiting_verification', $issue->fresh()->status);
    }

    public function test_import_is_repeatable_and_file_count_is_correct(): void
    {
        Storage::fake('local');
        $this->userFor('main_assy');
        $this->userFor('auto_line');
        $this->seed(QicVisaSeeder::class);
        $this->seed(QicVisaSeeder::class);
        $this->assertDatabaseCount('qic_issues', 11);
        $this->assertDatabaseCount('qic_evidence', 35);
        $this->assertDatabaseCount('qic_responses', 1);
        $this->assertDatabaseCount('qic_audits', 11);
        foreach (QicEvidence::all() as $file) {
            Storage::disk('local')->assertExists($file->path);
            $this->assertSame($file->sha256, hash_file('sha256', Storage::disk('local')->path($file->path)));
        }
    }

    public function test_new_3c_versions_preserve_previous_response(): void
    {
        $reporter = $this->userFor('main_assy');
        $owner = $this->userFor('auto_line');
        $quality = $this->userFor('quality');
        $issue = $this->issue($reporter, $owner, 'investigation');
        $issue->update(['investigation' => 'Hipotesis simulasi', 'containment' => 'Pisahkan sampel']);
        $data = ['workspace' => 'auto-line', 'action' => 'submit_3c', 'concern' => 'Pin bengkok', 'cause' => 'Hipotesis tray', 'countermeasure' => 'Ganti tray'];
        $this->actingAs($owner)->post(route('qic.issues.action', $issue->code), $data)->assertRedirect();
        $this->actingAs($quality)->post(route('qic.issues.action', $issue->code), ['workspace' => 'quality', 'action' => 'revise', 'note' => 'Lengkapi bukti'])->assertRedirect();
        $this->actingAs($owner)->post(route('qic.issues.action', $issue->code), $data)->assertRedirect();
        $this->assertSame([2, 1], $issue->responses()->pluck('version')->all());
        $this->assertSame('revise', $issue->responses()->where('version', 1)->first()->decision);
    }

    public function test_all_workspace_pages_render_using_database(): void
    {
        Storage::fake('local');
        $reporter = $this->userFor('main_assy');
        $this->userFor('auto_line');
        $master = $this->userFor('project_owner');
        $this->seed(QicVisaSeeder::class);
        $draft = $this->issue($reporter, null, 'draft');
        $sample = QicIssue::where('status', 'waiting_review')->firstOrFail();
        $this->actingAs($master);
        foreach (config('qic_ui') as $role => $definition) {
            foreach ($definition['pages'] as $key => $page) {
                $arguments = str_contains($page['path'], '{issue}')
                    ? ['issue' => $key === 'issues.edit' ? $draft->code : $sample->code] : [];
                $this->get(route($role.'.'.$key, $arguments))->assertOk();
            }
        }
    }

    public function test_role_menus_have_distinct_database_content(): void
    {
        Storage::fake('local');
        $this->userFor('main_assy');
        $this->userFor('auto_line');
        $master = $this->userFor('project_owner');
        $this->seed(QicVisaSeeder::class);
        $this->actingAs($master);
        $this->get(route('main-assy.dashboard'))->assertOk()->assertSee('Pantau complaint Anda');
        $this->get(route('auto-line.dashboard'))->assertOk()->assertSee('Pusat Penanganan Auto Line');
        $this->get(route('quality.dashboard'))->assertOk()->assertSee('Pusat Kendali Quality');
        $this->get(route('main-assy.tracking'))->assertOk()->assertSee('Perjalanan Complaint')->assertSee('QI-001');
        $this->get(route('auto-line.responses.index'))->assertOk()->assertSee('versi tersimpan');
        $this->get(route('quality.reviews.index'))->assertOk()->assertSee('Tinjau Respons dan Evidence');
        $this->get(route('quality.reports'))->assertOk()->assertSee('Distribusi Status')->assertSee('Jenis Defect');
        $this->get(route('quality.notifications'))->assertOk()->assertSee('Aktivitas Issue Terbaru')->assertSee('Impor');
        $this->get(route('quality.sla-monitoring'))->assertOk()->assertSee('Aturan SLA belum dikonfigurasi');
    }

    public function test_full_workflow_stores_corrective_file_and_closes_only_after_review(): void
    {
        Storage::fake('local');
        $reporter = $this->userFor('main_assy');
        $owner = $this->userFor('auto_line');
        $quality = $this->userFor('quality');
        $issue = $this->issue($reporter);
        $url = route('qic.issues.action', $issue->code);
        $this->actingAs($quality)->post($url, ['workspace' => 'quality', 'action' => 'verify'])->assertRedirect();
        $this->actingAs($quality)->post($url, ['workspace' => 'quality', 'action' => 'assign', 'owner_id' => $owner->id])->assertRedirect();
        $this->actingAs($owner)->post($url, ['workspace' => 'auto-line', 'action' => 'acknowledge'])->assertRedirect();
        $this->actingAs($owner)->post($url, ['workspace' => 'auto-line', 'action' => 'investigate', 'investigation' => 'Simulasi investigasi', 'containment' => 'Pisahkan sampel'])->assertRedirect();
        $this->actingAs($owner)->post($url, ['workspace' => 'auto-line', 'action' => 'submit_3c', 'concern' => 'Pin bengkok', 'cause' => 'Hipotesis simulasi', 'countermeasure' => 'Gunakan pelindung'])->assertRedirect();
        $this->actingAs($quality)->post($url, ['workspace' => 'quality', 'action' => 'approve', 'note' => 'Rencana simulasi disetujui'])->assertRedirect();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j2b0AAAAASUVORK5CYII=');
        $this->actingAs($owner)->post($url, ['workspace' => 'auto-line', 'action' => 'corrective', 'corrective_action' => 'Pengujian tindakan', 'corrective_result' => 'Hasil simulasi', 'evidence' => [UploadedFile::fake()->createWithContent('hasil.png', $png)]])->assertRedirect();
        $this->actingAs($quality)->post($url, ['workspace' => 'quality', 'action' => 'close', 'note' => 'Hasil simulasi diperiksa'])->assertRedirect();
        $this->assertSame('closed', $issue->fresh()->status);
        $this->assertDatabaseCount('qic_audits', 8);
        $this->assertSame('corrective', $issue->evidence()->first()->category);
    }
}
