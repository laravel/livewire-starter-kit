<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea la tabla de items del Packing Slip.
     * Cada item representa un lote incluido en el PS con sus datos de envio.
     *
     * Principio de snapshot inmutable:
     *   Los campos calculados (quantity_packed, wo_number_ps, lot_date_code, label_spec)
     *   se copian al momento de crear el PS y no se recalculan. Esto garantiza que el
     *   documento impreso siempre muestre los valores que tenia en el momento del despacho.
     *
     * Campos de precio (unit_price, price_tier_id, price_source):
     *   Son NULL hasta que se genere el Invoice FPL-12 (Fase 3).
     *   Se agregan aqui para evitar una migracion adicional en Fase 3.
     *   Ver: 06_impacto_respuestas_pendientes_y_ajustes.md, seccion 4.4
     */
    public function up(): void
    {
        Schema::create('packing_slip_items', function (Blueprint $table) {
            $table->id();

            // FK al Packing Slip
            $table->foreignId('packing_slip_id')
                  ->constrained('packing_slips')
                  ->onDelete('cascade')
                  ->comment('Packing Slip al que pertenece este item');

            // FK al lote. UNIQUE: un lote solo puede estar en un PS a la vez.
            // La constraint unique previene duplicados y sirve como guard de negocio.
            $table->foreignId('lot_id')
                  ->unique()
                  ->constrained('lots')
                  ->onDelete('restrict')
                  ->comment('Lote incluido en este PS. Un lote solo puede estar en un PS.');

            // Snapshot de la cantidad empacada al crear el PS.
            // Viene de lots.quantity_packed_final (calculado por LotPackagingObserver).
            $table->integer('quantity_packed')
                  ->comment('Snapshot de la cantidad empacada (lots.quantity_packed_final) al crear el PS');

            // Snapshot del numero de WO para el FPL-10.
            // Formato: "W0" + work_orders.external_wo_number + lot_seq_padded_3_digits
            // Ej: "W0" + "1234567" + "001" = "W012345670001" (si el lot_number es "001")
            // NULL si el WO no tiene external_wo_number (decision D-06-05).
            $table->string('wo_number_ps', 30)
                  ->nullable()
                  ->comment('Snapshot del codigo de WO para FPL-10. Formato: W0 + external_wo_number + lot_seq');

            // Snapshot del date code del lote para la columna G del FPL-10.
            // DECISION PROVISIONAL D-06-01: usar lots.lot_number hasta confirmar con S.E.I.P., Inc.
            // TODO(P-06-01): Confirmar con S.E.I.P., Inc. el dato exacto esperado en la columna G del FPL-10
            //   antes de lanzar el PDF a produccion. Opciones: lot_number, fecha del PO, fecha de manufactura, otro.
            $table->string('lot_date_code', 20)
                  ->nullable()
                  ->comment('Snapshot del date code del lote para columna G del FPL-10 (provisional = lot_number)');

            // Especificacion de etiqueta militar/aeronautica (ej: M83519/2-8).
            // DECISION D-06-02: Ingreso manual en el wizard. Campo opcional.
            // Sin vinculo con parts tabla en esta fase. Editable solo en estado draft.
            $table->string('label_spec', 50)
                  ->nullable()
                  ->comment('Especificacion de etiqueta (M83519/2-8 etc). Ingreso manual, opcional.');

            // =====================================================================
            // CAMPOS DEL INVOICE FPL-12 (se llenan en Fase 3, son NULL en Fase 1/2)
            // =====================================================================

            // Snapshot del precio unitario al generar el Invoice.
            // Se calcula via Price::getActivePriceForPart($partId)->getPriceForQuantity($poQuantity).
            // La cantidad de referencia es la cantidad de la PO (no del lote individual).
            $table->decimal('unit_price', 10, 4)
                  ->nullable()
                  ->comment('Snapshot del precio unitario al generar Invoice FPL-12 (NULL hasta Fase 3)');

            // Referencia al tier de precio utilizado para auditoria.
            $table->foreignId('price_tier_id')
                  ->nullable()
                  ->constrained('price_tiers')
                  ->nullOnDelete()
                  ->comment('Tier de precio usado para calcular unit_price (auditoria)');

            // Indica la fuente del precio para el Invoice.
            // tier = calculado por rango de cantidad
            // sample = fallback al sample_price de la tabla prices
            // manual = ingresado manualmente por el admin (correccion)
            $table->enum('price_source', ['tier', 'sample', 'manual'])
                  ->nullable()
                  ->comment('Fuente del unit_price: tier|sample|manual (NULL hasta Fase 3)');

            $table->timestamps();

            // Indices
            $table->index('packing_slip_id', 'idx_psi_packing_slip_id');
            // lot_id ya tiene indice por el unique constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_slip_items');
    }
};
