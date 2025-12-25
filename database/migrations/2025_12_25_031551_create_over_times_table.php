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
        Schema::create('over_times', function (Blueprint $table) {
            $table->id();

            // Información básica
            $table->string('name')->comment('Nombre descriptivo del overtime');

            // Horario
            $table->time('start_time')->comment('Hora de inicio del overtime');
            $table->time('end_time')->comment('Hora de fin del overtime');
            $table->integer('break_minutes')
                  ->default(0)
                  ->comment('Minutos de descanso durante el overtime');

            // Recursos
            $table->integer('employees_qty')
                  ->comment('Cantidad de empleados disponibles');

            // Fecha
            $table->date('date')->comment('Fecha específica del overtime');

            // Relaciones
            $table->foreignId('shift_id')
                  ->constrained('shifts')
                  ->cascadeOnDelete()
                  ->comment('Turno al que pertenece este overtime');

            // Comentarios
            $table->text('comments')->nullable();

            // Timestamps
            $table->timestamps();

            // Índices para optimización de queries
            $table->index('shift_id', 'idx_over_times_shift');
            $table->index('date', 'idx_over_times_date');
            $table->index(['shift_id', 'date'], 'idx_over_times_shift_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('over_times');
    }
};
