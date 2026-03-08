<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea la tabla de Packing Slips (FPL-10).
     * Un Packing Slip agrupa uno o mas lotes para un envio a S.E.I.P., Inc.
     * La relacion con Invoice FPL-12 es 1:1 (un PS genera un Invoice).
     *
     * Ciclo de vida del PS:
     *   draft -> confirmed -> shipped
     *
     * Solo Admin y Shipping pueden crear/confirmar/despachar.
     * Empaque tiene acceso de solo lectura (D-06-04).
     */
    public function up(): void
    {
        Schema::create('packing_slips', function (Blueprint $table) {
            $table->id();

            // Numero unico del Packing Slip (ej: PS-2026-0001)
            $table->string('ps_number', 30)->unique()
                  ->comment('Numero unico del Packing Slip, generado automaticamente');

            // Usuario que creo el PS
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->comment('Usuario que creo el Packing Slip');

            // Estado del ciclo de vida del PS
            $table->enum('status', ['draft', 'confirmed', 'shipped'])
                  ->default('draft')
                  ->comment('Estado del PS: draft|confirmed|shipped');

            // Timestamp de cuando el PS fue despachado (status = shipped)
            $table->timestamp('shipped_at')
                  ->nullable()
                  ->comment('Momento en que el PS fue despachado a S.E.I.P., Inc.');

            // Usuario que realizo el despacho
            $table->foreignId('shipped_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('Usuario que marco el PS como shipped');

            // Notas adicionales para el PS
            $table->text('notes')
                  ->nullable()
                  ->comment('Notas adicionales del Packing Slip');

            $table->timestamps();
            $table->softDeletes();

            // Indices
            $table->index('status', 'idx_packing_slips_status');
            $table->index('shipped_at', 'idx_packing_slips_shipped_at');
            $table->index('created_by', 'idx_packing_slips_created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_slips');
    }
};
