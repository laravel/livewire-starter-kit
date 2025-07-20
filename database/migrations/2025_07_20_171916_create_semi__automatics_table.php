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
        Schema::create('semi__automatics', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->integer('employees');
            $table->boolean('active');
            $table->string('comments')->nullable();

            $table->foreignId('area_id')->constrained('areas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semi__automatics');
    }
};
