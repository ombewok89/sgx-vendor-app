<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('spk_number')->unique();
            $table->string('title');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('job_type_id')->nullable()->constrained('job_types')->nullOnDelete();
            $table->string('location_name');
            $table->decimal('target_lat', 10, 7)->nullable();
            $table->decimal('target_lng', 10, 7)->nullable();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->date('deadline');
            $table->string('doc_mode')->default('BEFORE_PROCESS_AFTER');
            $table->boolean('require_checkin')->default(true);
            $table->string('status')->default('DRAFT'); // DRAFT, ASSIGNED, IN_PROGRESS, REVIEW, REVISION, APPROVED, COMPLETED, CANCELLED
            $table->integer('progress_percent')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('work_order_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_in_team')->default('MEMBER'); // PIC, MEMBER
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            $table->unique(['work_order_id', 'user_id']);
        });

        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->string('item_name');
            $table->foreignId('job_type_id')->nullable()->constrained('job_types')->nullOnDelete();
            $table->string('doc_mode')->default('BEFORE_PROCESS_AFTER');
            $table->integer('weight_percent')->default(100);
            $table->string('status')->default('PENDING'); // PENDING, IN_PROGRESS, COMPLETED
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
        Schema::dropIfExists('work_order_user');
        Schema::dropIfExists('work_orders');
    }
};
