<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migracion para agregar campo is_migrated a la tabla standards
 *
 * Este campo se utiliza como control de migracion para rastrear cuales
 * standards ya han sido migrados a la nueva estructura de standard_configurations.
 *
 * Estrategia de migracion gradual:
 * 1. Los campos existentes (persons_1, persons_2, persons_3, units_per_hour) se mantienen
 * 2. Se usa is_migrated para identificar standards que ya tienen configuraciones
 * 3. Permite operar con ambos sistemas durante la transicion
 *
 * Referencia: Spec 06 - Plan de Migracion de Datos
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            $table->boolean('is_migrated')
                  ->default(false)
                  ->after('active')
                  ->comment('Indica si el standard ha sido migrado a standard_configurations');

            // Indice para filtrar standards migrados/no migrados
            $table->index('is_migrated', 'idx_standards_migrated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            $table->dropIndex('idx_standards_migrated');
            $table->dropColumn('is_migrated');
        });
    }
};
