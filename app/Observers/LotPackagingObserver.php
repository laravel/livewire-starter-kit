<?php

namespace App\Observers;

use App\Models\Lot;
use Illuminate\Support\Facades\Log;

/**
 * LotPackagingObserver
 *
 * Escucha los cambios en el modelo Lot para detectar el momento exacto en que
 * un lote es cerrado por Empaque (closure_decision se establece). Cuando eso
 * sucede, calcula quantity_packed_final y activa ready_for_shipping = true,
 * enviando el lote a la cola de Shipping.
 *
 * Los tres tipos de cierre soportados (decision D-01 del plan):
 *   - complete_lot : El lote se completó sin sobrante. Listo para PS.
 *   - new_lot      : El sobrante se convirtió en un nuevo lote. El lote original queda listo.
 *   - close_as_is  : El lote se cerró tal como estaba (con o sin sobrante). Listo para PS.
 *
 * CUANDO SE DISPARA:
 *   El observer escucha el evento 'updated' del modelo Lot.
 *   Solo actua cuando closure_decision cambia de NULL a uno de los 3 valores validos.
 *   No actua en otras actualizaciones del lote (evita doble ejecucion).
 *
 * IDEMPOTENCIA:
 *   Si por alguna razon el observer se ejecuta dos veces (ej: retry de transaccion),
 *   la condicion `ready_for_shipping === false` previene duplicar la logica.
 *
 * REVERSION BLOQUEADA (decision D-12):
 *   Si el lote ya esta asignado a un Packing Slip (packing_slip_items.lot_id),
 *   la reversion del closure_decision debe ser bloqueada por el componente Livewire
 *   o el servicio que lo gestiona (no es responsabilidad de este observer).
 */
class LotPackagingObserver
{
    /**
     * Handle the Lot "updated" event.
     * Solo actua cuando closure_decision cambia a un valor de cierre valido.
     */
    public function updated(Lot $lot): void
    {
        // Solo actuar si closure_decision acaba de cambiar
        if (!$lot->wasChanged('closure_decision')) {
            return;
        }

        $closureDecision = $lot->closure_decision;

        // Verificar que el nuevo valor es uno de los 3 tipos de cierre validos
        $validClosureTypes = [
            Lot::CLOSURE_COMPLETE_LOT,
            Lot::CLOSURE_NEW_LOT,
            Lot::CLOSURE_CLOSE_AS_IS,
        ];

        if (!in_array($closureDecision, $validClosureTypes, strict: true)) {
            return; // No es un cierre valido o se establecio a NULL (reversion)
        }

        // Idempotencia: no recalcular si ya esta marcado como listo
        if ($lot->ready_for_shipping === true) {
            Log::info('LotPackagingObserver: lote ya estaba marcado como ready_for_shipping, saltando.', [
                'lot_id' => $lot->id,
                'lot_number' => $lot->lot_number,
            ]);
            return;
        }

        // Calcular la cantidad empacada final sumando todos los packaging_records del lote.
        // Se usa la relacion directa para obtener el valor fresco desde la BD.
        $quantityPackedFinal = (int) $lot->packagingRecords()->sum('packed_pieces');

        Log::info('LotPackagingObserver: activando ready_for_shipping para lote.', [
            'lot_id'               => $lot->id,
            'lot_number'           => $lot->lot_number,
            'closure_decision'     => $closureDecision,
            'quantity_packed_final' => $quantityPackedFinal,
        ]);

        // Actualizar sin disparar nuevamente el observer (updateQuietly evita el loop)
        $lot->updateQuietly([
            'quantity_packed_final' => $quantityPackedFinal,
            'ready_for_shipping'    => true,
            'ready_for_shipping_at' => now(),
            'closed_by_type'        => $closureDecision,
        ]);

        Log::info('LotPackagingObserver: lote marcado como ready_for_shipping.', [
            'lot_id'     => $lot->id,
            'ps_queue'   => "lot #{$lot->id} disponible en cola de shipping",
        ]);
    }
}
