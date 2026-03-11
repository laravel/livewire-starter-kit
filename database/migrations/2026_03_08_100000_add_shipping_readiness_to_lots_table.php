<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrega los campos necesarios para la transicion de Empaque -> Shipping List.
     * Estos campos son calculados y persistidos por LotPackagingObserver al momento
     * del cierre del lote (cualquiera de los 3 tipos: complete_lot, new_lot, close_as_is).
     */
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            // Cantidad real empacada: SUM(packaging_records.packed_pieces) al cierre del lote.
            // Se persiste como snapshot para que el Packing Slip no dependa de recalcular.
            $table->integer('quantity_packed_final')
                  ->nullable()
                  ->after('quantity')
                  ->comment('Suma real de packed_pieces de packaging_records al cierre del lote');

            // Flag que indica si el lote esta listo para incluirse en un Packing Slip.
            // Se activa mediante LotPackagingObserver al detectar el closure_decision.
            $table->boolean('ready_for_shipping')
                  ->default(false)
                  ->after('quantity_packed_final')
                  ->comment('True cuando el lote ha sido cerrado y puede incluirse en un Packing Slip');

            // Timestamp de cuando el lote paso a ready_for_shipping = true.
            $table->timestamp('ready_for_shipping_at')
                  ->nullable()
                  ->after('ready_for_shipping')
                  ->comment('Momento en que el lote fue marcado como listo para shipping');

            // Tipo de cierre que activo el estado ready_for_shipping.
            // Replica el closure_decision con solo los valores validos del negocio.
            // Se persiste aqui para dejar trazabilidad clara en el contexto de shipping.
            $table->enum('closed_by_type', ['complete_lot', 'new_lot', 'close_as_is'])
                  ->nullable()
                  ->after('ready_for_shipping_at')
                  ->comment('Tipo de cierre de empaque que activo ready_for_shipping');

            // Indice para la cola de shipping: busca lotes listos que no esten en un PS.
            // La condicion "no en PS" se evalua via packing_slip_items.lot_id (Fase 1.3).
            $table->index(['ready_for_shipping', 'ready_for_shipping_at'], 'idx_lots_shipping_queue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropIndex('idx_lots_shipping_queue');
            $table->dropColumn([
                'quantity_packed_final',
                'ready_for_shipping',
                'ready_for_shipping_at',
                'closed_by_type',
            ]);
        });
    }
};
