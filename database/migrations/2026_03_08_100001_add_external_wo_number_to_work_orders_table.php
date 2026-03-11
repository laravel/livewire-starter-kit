<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrega el numero externo de WO (7 digitos) que usa FPL-10 para construir
     * el codigo de WO en el Packing Slip (formato: "W0" + external_wo_number + lot_seq_padded).
     *
     * DECISION D-06-05: Solo se pobla para WOs nuevos creados a partir de esta fase.
     * Los WOs historicos quedan con external_wo_number = NULL.
     * PackingSlipService valida que todos los lotes de un PS tengan WO con este campo.
     */
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('external_wo_number', 20)
                  ->nullable()
                  ->after('wo_number')
                  ->comment('Numero externo de 7 digitos para FPL-10. Solo WOs nuevos. Historicos = NULL.');

            $table->index('external_wo_number', 'idx_work_orders_external_wo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex('idx_work_orders_external_wo');
            $table->dropColumn('external_wo_number');
        });
    }
};
