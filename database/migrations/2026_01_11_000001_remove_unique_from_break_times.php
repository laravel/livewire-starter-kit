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
     *
     * NOTA: Esta migración es parcialmente irreversible porque los datos
     * existentes ya no son compatibles con los índices únicos originales
     * (hay múltiples break_times con el mismo nombre en diferentes turnos).
     *
     * El rollback solo elimina el índice compuesto y recrea la FK,
     * pero NO restaura los índices únicos originales.
     */
    public function down(): void
    {
        Schema::table('break_times', function (Blueprint $table) {
            // 1. Eliminar la foreign key primero (necesita el índice)
            $table->dropForeign(['shift_id']);

            // 2. Eliminar el índice compuesto
            $table->dropUnique('break_times_shift_name_unique');

            // 3. Crear índice simple en shift_id para la FK
            $table->index('shift_id', 'break_times_shift_id_index');

            // 4. Recrear la foreign key
            $table->foreign('shift_id')
                  ->references('id')
                  ->on('shifts')
                  ->onDelete('cascade');

            // NOTA: No se restauran los índices únicos originales porque
            // los datos existentes tienen duplicados (ej: múltiples "Comida")
            // Si se necesita restaurar la estructura original, primero
            // eliminar datos duplicados manualmente.
        });
    }
};
