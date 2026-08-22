<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('work_orders', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('progress_percent');
            }
            if (!Schema::hasColumn('work_orders', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('is_archived');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            if (Schema::hasColumn('work_orders', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
            if (Schema::hasColumn('work_orders', 'is_archived')) {
                $table->dropColumn('is_archived');
            }
        });
    }
};
