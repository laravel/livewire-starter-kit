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
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('sn')->nullable();
            $table->string('asset_number')->nullable()->unique();
            $table->integer('employees');
            $table->decimal('setup_time', 8, 2);
            $table->decimal('maintenance_time', 8, 2);
            $table->boolean('active');
            $table->text('comments')->nullable();

            // NO puedes eliminar un área si tiene máquinas (RESTRICT)
            $table->foreignId('area_id')->constrained('areas');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
