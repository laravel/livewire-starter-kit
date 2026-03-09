<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrega el campo document_date a la tabla packing_slips.
     * Este campo representa la fecha del documento FPL-10 (campo DATE del header del Excel),
     * que puede ser distinta a created_at cuando el documento se registra
     * en el sistema en una fecha diferente a la del envio fisico.
     *
     * Ver analisis: 07_fpl10_cumplimiento_vs_implementacion.md, punto M-1.
     */
    public function up(): void
    {
        Schema::table('packing_slips', function (Blueprint $table) {
            $table->date('document_date')
                  ->nullable()
                  ->after('status')
                  ->comment('Fecha del documento FPL-10 (campo DATE del header del Excel). Distinta de created_at cuando el PS se registra retroactivamente.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packing_slips', function (Blueprint $table) {
            $table->dropColumn('document_date');
        });
    }
};
