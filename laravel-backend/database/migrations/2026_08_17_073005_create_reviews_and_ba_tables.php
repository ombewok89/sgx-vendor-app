<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users');
            $table->string('status'); // APPROVED, REVISION_REQUESTED, REJECTED
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('review_id')->nullable()->constrained('reviews')->nullOnDelete();
            $table->string('target_stage'); // BEFORE, PROCESS, AFTER
            $table->text('reason');
            $table->foreignId('requested_by')->constrained('users');
            $table->timestamp('requested_at')->useCurrent();
            $table->string('status')->default('OPEN'); // OPEN, RESOLVED
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->longText('header_html')->nullable();
            $table->longText('footer_html')->nullable();
            $table->longText('body_template')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('ba_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->unique()->constrained('work_orders')->cascadeOnDelete();
            $table->string('ba_number')->unique();
            $table->date('ba_date');
            $table->foreignId('template_id')->nullable()->constrained('document_templates')->nullOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('content_json')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('status')->default('FINAL'); // DRAFT, FINAL, SIGNED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ba_documents');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('revisions');
        Schema::dropIfExists('reviews');
    }
};
