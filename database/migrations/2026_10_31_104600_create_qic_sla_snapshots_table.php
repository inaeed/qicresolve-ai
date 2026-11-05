<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qic_sla_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('issue_id')
                ->constrained('qic_issues')
                ->restrictOnDelete();

            $table->string('rule_id', 20);
            $table->unsignedInteger('cycle');
            $table->string('rule_version', 100);

            // Menyimpan aturan dan kalender yang digunakan.
            $table->json('rule_snapshot');
            $table->json('calendar_snapshot');

            // Menyimpan hasil evaluasi tanpa pembulatan ulang.
            $table->json('evaluation');

            // Membedakan histori lama dengan pengukuran baru.
            $table->string('source', 40);

            $table->timestamp('captured_at');

            // Satu hasil final per aturan dan siklus pada satu issue.
            $table->unique(
                ['issue_id', 'rule_id', 'cycle'],
                'qic_sla_snapshot_cycle_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qic_sla_snapshots');
    }
};
