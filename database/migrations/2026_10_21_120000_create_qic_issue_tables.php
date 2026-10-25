<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qic_issues', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('seed_key')->nullable()->unique();
            $table->foreignId('reporter_id')->constrained('users');
            $table->foreignId('owner_id')->nullable()->constrained('users');
            $table->string('material');
            $table->string('material_name')->nullable();
            $table->string('batch')->nullable();
            $table->string('defect');
            $table->unsignedInteger('quantity');
            $table->string('priority')->default('Normal');
            $table->text('description');
            $table->timestamp('occurred_at');
            $table->string('status')->default('waiting_verification')->index();
            $table->text('investigation')->nullable();
            $table->text('containment')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('corrective_result')->nullable();
            $table->string('source_file')->nullable();
            $table->text('source_note')->nullable();
            $table->timestamps();
        });
        Schema::create('qic_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('qic_issues')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users');
            $table->unsignedInteger('version');
            $table->text('concern');
            $table->text('cause');
            $table->text('countermeasure');
            $table->string('decision')->nullable();
            $table->text('review_note')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['issue_id', 'version']);
        });
        Schema::create('qic_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('qic_issues')->cascadeOnDelete();
            $table->foreignId('response_id')->nullable()->constrained('qic_responses')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('category');
            $table->string('original_name');
            $table->string('path')->unique();
            $table->string('mime');
            $table->unsignedBigInteger('size');
            $table->string('sha256', 64);
            $table->timestamps();
        });
        Schema::create('qic_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('qic_issues')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users');
            $table->string('action');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qic_audits');
        Schema::dropIfExists('qic_evidence');
        Schema::dropIfExists('qic_responses');
        Schema::dropIfExists('qic_issues');
    }
};
