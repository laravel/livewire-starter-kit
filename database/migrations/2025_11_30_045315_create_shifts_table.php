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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->tinyInteger('active')->default(1);
            $table->text('comments')->nullable();

            $table->timestamps();

            // Índices para mejorar búsquedas
            $table->index('name');
            $table->index('active');
            $table->index(['active', 'name']);
            $table->index(['active', 'start_time']);
            $table->index(['active', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
