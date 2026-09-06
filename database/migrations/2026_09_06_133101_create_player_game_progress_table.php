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
        Schema::create('player_game_progress', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('mystery_case_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('current_chapter_id')
                ->nullable()
                ->constrained('chapters')
                ->nullOnDelete();

            $table->foreignId('current_scene_id')
                ->nullable()
                ->constrained('scenes')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['user_id', 'mystery_case_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_game_progress');
    }
};
