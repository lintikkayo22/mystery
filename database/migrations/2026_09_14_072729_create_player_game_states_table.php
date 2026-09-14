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
        Schema::create('player_game_states', function (Blueprint $table) {
            $table->id();

            $table->foreignId('player_game_progress_id')
                ->constrained('player_game_progress')
                ->cascadeOnDelete();

            $table->string('key');
            $table->string('value');

            $table->timestamps();

            $table->unique([
                'player_game_progress_id',
                'key',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_game_states');
    }
};
