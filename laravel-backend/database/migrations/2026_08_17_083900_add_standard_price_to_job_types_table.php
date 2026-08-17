<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_types') && !Schema::hasColumn('job_types', 'standard_price')) {
            Schema::table('job_types', function (Blueprint $table) {
                $table->decimal('standard_price', 15, 2)->default(0)->after('min_photos_per_stage');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('job_types') && Schema::hasColumn('job_types', 'standard_price')) {
            Schema::table('job_types', function (Blueprint $table) {
                $table->dropColumn('standard_price');
            });
        }
    }
};
