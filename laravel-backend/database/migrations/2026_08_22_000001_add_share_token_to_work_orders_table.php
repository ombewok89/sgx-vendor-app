<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('work_orders', 'share_token')) {
                $table->string('share_token', 64)->nullable()->unique()->after('notes');
            }
            if (!Schema::hasColumn('work_orders', 'is_shareable')) {
                $table->boolean('is_shareable')->default(true)->after('share_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            if (Schema::hasColumn('work_orders', 'share_token')) {
                $table->dropColumn('share_token');
            }
            if (Schema::hasColumn('work_orders', 'is_shareable')) {
                $table->dropColumn('is_shareable');
            }
        });
    }
};
