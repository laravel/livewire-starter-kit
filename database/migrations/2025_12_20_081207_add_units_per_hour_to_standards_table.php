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
        Schema::table('standards', function (Blueprint $table) {
            // Agregar campo units_per_hour después de part_id
            $table->integer('units_per_hour')
                  ->after('part_id')
                  ->default(1)
                  ->comment('Unidades producidas por hora en esta estación');

            // Índice compuesto para optimizar búsquedas de capacidad
            $table->index(
                ['part_id', 'active', 'units_per_hour'],
                'standards_part_performance_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            $table->dropIndex('standards_part_performance_index');
            $table->dropColumn('units_per_hour');
        });
    }
};
