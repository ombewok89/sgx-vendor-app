<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('work_order_items', 'is_addendum')) {
                $table->boolean('is_addendum')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('work_order_items', 'is_addendum')) {
                $table->dropColumn('is_addendum');
            }
        });
    }
};
