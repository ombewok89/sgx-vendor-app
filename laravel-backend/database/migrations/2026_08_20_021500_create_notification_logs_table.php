<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->default('WHATSAPP');
            $table->string('provider')->default('FONNTE');
            $table->string('recipient');
            $table->string('message_type'); // e.g. WORK_ORDER_CREATED, TEST_MESSAGE
            $table->string('reference_type')->nullable(); // e.g. WORK_ORDER, USER
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('idempotency_key')->nullable()->index();
            $table->text('payload')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, SENT, FAILED, SKIPPED
            $table->text('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['channel', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
