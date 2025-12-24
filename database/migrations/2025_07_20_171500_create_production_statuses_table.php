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
        Schema::create('production_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color', 7); // Hex color #RRGGBB
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices para mejorar performance de queries
            $table->index('active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_statuses');
    }
};
