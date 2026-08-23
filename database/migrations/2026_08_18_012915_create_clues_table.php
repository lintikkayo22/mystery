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
        Schema::create('clues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mystery_case_id')
                    ->constrained('mystery_cases')
                    ->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->string('type');
            $table->boolean('is_revealed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clues');
    }
};
