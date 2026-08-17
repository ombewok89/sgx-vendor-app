<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('server_timestamp')->useCurrent();
            $table->string('client_timestamp')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 8, 2);
            $table->string('address_note')->nullable();
            $table->timestamps();
        });

        Schema::create('evidence_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('work_order_items')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('stage'); // BEFORE, PROCESS, AFTER, ISSUE
            $table->integer('sequence')->default(1);
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size');
            $table->string('mime_type');
            $table->string('file_hash'); // SHA-256
            $table->timestamp('server_timestamp')->useCurrent();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('has_issue')->default(false);
            $table->string('issue_type')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('OPEN'); // OPEN, RESOLVED
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
        Schema::dropIfExists('evidence_photos');
        Schema::dropIfExists('check_ins');
    }
};
