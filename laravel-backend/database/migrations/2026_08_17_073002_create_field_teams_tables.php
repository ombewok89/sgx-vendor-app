<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('leader_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('field_team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_team_id')->constrained('field_teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['field_team_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_team_user');
        Schema::dropIfExists('field_teams');
    }
};
