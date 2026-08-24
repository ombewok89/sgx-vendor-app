<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'logo_url')) {
                $table->string('logo_url')->nullable()->after('email');
            }
            if (!Schema::hasColumn('vendors', 'banner_url')) {
                $table->string('banner_url')->nullable()->after('logo_url');
            }
            if (!Schema::hasColumn('vendors', 'npwp')) {
                $table->string('npwp')->nullable()->after('banner_url');
            }
            if (!Schema::hasColumn('vendors', 'website')) {
                $table->string('website')->nullable()->after('npwp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('vendors', 'logo_url')) $columns[] = 'logo_url';
            if (Schema::hasColumn('vendors', 'banner_url')) $columns[] = 'banner_url';
            if (Schema::hasColumn('vendors', 'npwp')) $columns[] = 'npwp';
            if (Schema::hasColumn('vendors', 'website')) $columns[] = 'website';
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
