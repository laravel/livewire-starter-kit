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
        Schema::create('parts', function (Blueprint $table) {
            $table->id();

            $table->string('number')->unique();
            $table->string('item_number')->unique();
            $table->string('unit_of_measure')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->text('description')->nullable();
            $table->string('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['number', 'active', 'item_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
