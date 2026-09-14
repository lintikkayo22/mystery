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
        Schema::create('player_evidences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('player_game_progress_id')
                ->constrained('player_game_progress')
                ->cascadeOnDelete();

            $table->foreignId('evidence_id')
                ->constrained('evidence')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'player_game_progress_id',
                'evidence_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_evidences');
    }
};
