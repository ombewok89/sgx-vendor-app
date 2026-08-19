<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_logs', 'attempts')) {
                $table->unsignedSmallInteger('attempts')->default(1)->after('status');
            }
            if (!Schema::hasColumn('notification_logs', 'failure_type')) {
                $table->string('failure_type')->nullable()->after('attempts'); // TEMPORARY, PERMANENT
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropColumn(['attempts', 'failure_type']);
        });
    }
};
