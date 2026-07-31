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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->json('criteria');
        });

        Schema::create('child_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');

            $table->unique(['child_profile_id', 'badge_id']);
        });

        Schema::create('guardians_children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('child_profile_id')->constrained()->cascadeOnDelete();
            $table->string('relationship_type')->default('tutor');

            $table->unique(['guardian_id', 'child_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians_children');
        Schema::dropIfExists('child_badges');
        Schema::dropIfExists('badges');
    }
};
