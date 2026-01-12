<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remueve las restricciones unique de break_times que impiden
     * tener múltiples descansos con el mismo nombre/hora en diferentes turnos.
     */
    public function up(): void
    {
        Schema::table('break_times', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['start_break_time']);
            $table->dropUnique(['end_break_time']);
            
            // Agregar índice compuesto para evitar duplicados dentro del mismo turno
            $table->unique(['shift_id', 'name'], 'break_times_shift_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('break_times', function (Blueprint $table) {
            $table->dropUnique('break_times_shift_name_unique');
            
            $table->unique('name');
            $table->unique('start_break_time');
            $table->unique('end_break_time');
        });
    }
};
