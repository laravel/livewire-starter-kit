<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migracion para crear la tabla standard_configurations
 *
 * Esta tabla almacena las configuraciones individuales de produccion para cada standard.
 * Permite multiples configuraciones por standard, diferenciando por:
 * - Tipo de estacion de trabajo (manual, semi_automatic, machine)
 * - Cantidad de personas requeridas (1, 2 o 3 max)
 * - Productividad especifica (units_per_hour) para cada combinacion
 *
 * Referencia: Spec 06 - Multiple Standards por Numero de Parte
 *
 * @see \App\Models\StandardConfiguration
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('standard_configurations', function (Blueprint $table) {
            $table->id();

            // Relacion con standard padre
            $table->foreignId('standard_id')
                  ->constrained('standards')
                  ->onDelete('cascade')
                  ->comment('FK al standard padre');

            // Tipo de estacion de trabajo
            $table->enum('workstation_type', ['manual', 'semi_automatic', 'machine'])
                  ->comment('Tipo de estacion: manual=Mesa, semi_automatic=Semi-Auto, machine=Maquina');

            // ID de la estacion especifica (opcional, para vincular a estacion concreta)
            $table->unsignedBigInteger('workstation_id')
                  ->nullable()
                  ->comment('FK a la tabla de estacion correspondiente segun workstation_type');

            // Cantidad de personas requeridas (maximo 3)
            $table->unsignedTinyInteger('persons_required')
                  ->default(1)
                  ->comment('Numero de personas requeridas (1-3)');

            // Productividad en unidades por hora
            $table->unsignedInteger('units_per_hour')
                  ->comment('Productividad: unidades producidas por hora con esta configuracion');

            // Indicador de configuracion por defecto
            $table->boolean('is_default')
                  ->default(false)
                  ->comment('Indica si es la configuracion por defecto del standard');

            // Notas adicionales
            $table->text('notes')
                  ->nullable()
                  ->comment('Notas o comentarios sobre la configuracion');

            $table->timestamps();

            // ============================================
            // CONSTRAINTS
            // ============================================

            // Constraint de unicidad: no puede haber dos configuraciones iguales
            // para el mismo standard + tipo de estacion + cantidad de personas
            $table->unique(
                ['standard_id', 'workstation_type', 'persons_required'],
                'unique_standard_config'
            );

            // ============================================
            // INDICES PARA OPTIMIZACION DE CONSULTAS
            // ============================================

            // Indice para busquedas por standard_id
            $table->index('standard_id', 'idx_config_standard');

            // Indice para busquedas por tipo de estacion
            $table->index('workstation_type', 'idx_config_workstation_type');

            // Indice para busquedas por cantidad de personas
            $table->index('persons_required', 'idx_config_persons');

            // Indice compuesto para buscar configuracion por defecto de un standard
            $table->index(['standard_id', 'is_default'], 'idx_config_default');

            // Indice para ordenar por productividad
            $table->index('units_per_hour', 'idx_config_productivity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_configurations');
    }
};
