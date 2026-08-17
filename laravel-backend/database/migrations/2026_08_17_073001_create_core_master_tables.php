<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->timestamps();
        });

        Schema::create('job_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('doc_mode')->default('BEFORE_PROCESS_AFTER');
            $table->integer('min_photos_per_stage')->default(3);
            $table->decimal('standard_price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('vendor_id')->nullable()->after('phone')->constrained('vendors')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['phone', 'vendor_id', 'is_active']);
        });
        Schema::dropIfExists('job_types');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('vendors');
    }
};
