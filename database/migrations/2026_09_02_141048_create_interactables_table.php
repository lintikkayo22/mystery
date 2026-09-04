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
        Schema::create('interactables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scene_id')
                ->constrained('scenes')
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->enum('type', [
                'object',
                'door',
                'container',
                'decoration',
            ]);

            $table->unsignedDecimal('position_x', 5, 2);
            $table->unsignedDecimal('position_y', 5, 2);
            $table->unsignedDecimal('width', 5, 2);
            $table->unsignedDecimal('height', 5, 2);

            $table->enum('status', [
                'draft',
                'published',
            ])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interactables');
    }
};
