<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_logs')) {
            Schema::create('notification_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_feed_id')->nullable()->constrained('notifications_feed')->nullOnDelete();
                $table->string('recipient', 50);
                $table->string('message_type', 50)->default('CUSTOM');
                $table->text('message');
                $table->string('status', 20)->default('PENDING'); // SENT, FAILED, PENDING
                $table->string('fonnte_response_id')->nullable();
                $table->text('error_message')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index(['recipient', 'status']);
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
