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
        Schema::create('evidence', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mystery_case_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('title');
            $table->text('description');
            $table->string('type');
            $table->string('file_path')->nullable();
            $table->boolean('is_revealed')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence');
    }
};
