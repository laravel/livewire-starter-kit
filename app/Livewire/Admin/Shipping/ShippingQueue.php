<?php

namespace App\Livewire\Admin\Shipping;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Lot;
use App\Models\PackingSlip;
use App\Models\PackingSlipItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ShippingQueue — Cola de Despacho (Fase 1/2)
 *
 * Muestra los lotes marcados como ready_for_shipping = true que aun no han
 * sido asignados a ningun Packing Slip. Permite al usuario de Shipping
 * seleccionar lotes y crear un nuevo PS con ellos.
 *
 * PERMISOS (decision D-06-04):
 *   - Admin y Shipping: pueden seleccionar lotes y crear PS.
 *   - Empaque: solo vista (sin checkboxes ni boton de crear PS).
 *     TODO(D-06-04): Definir con Frank si Empaque puede crear PS en fases futuras.
 *
 * VALIDACION DE WO (decision D-06-05):
 *   - Si un WO no tiene external_wo_number, el lote se muestra con advertencia.
 *   - No puede incluirse en un PS hasta que el WO tenga external_wo_number.
 */
#[Layout('components.layouts.app')]
class ShippingQueue extends Component
{
    use WithPagination;

    // Filtros de la cola
    public string $searchTerm = '';
    public string $filterClosedByType = '';

    // Seleccion de lotes para crear PS
    public array $selectedLotIds = [];

    // Estado del wizard de creacion de PS
    public bool $showCreatePsModal = false;
    public string $psNotes = '';

    // Estado de los label_specs por lote seleccionado (ingreso manual, decision D-06-02)
    public array $labelSpecs = [];

    // Estado de la operacion
    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    // =========================================================
    // Lifecycle hooks
    // =========================================================

    public function updatedSearchTerm(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClosedByType(): void
    {
        $this->resetPage();
    }

    // =========================================================
    // Seleccion de lotes
    // =========================================================

    /**
     * Agrega o quita un lote de la seleccion.
     * Solo permite seleccionar lotes cuyo WO tiene external_wo_number.
     */
    public function toggleLot(int $lotId): void
    {
        $lot = Lot::with('workOrder')->find($lotId);

        if (!$lot) {
            return;
        }

        // Validar que el WO tiene numero externo (decision D-06-05)
        if (!$lot->workOrder->hasExternalWoNumber()) {
            $this->errorMessage = "El lote #{$lot->lot_number} no puede seleccionarse: su WO no tiene numero externo configurado. Edita la WO y asigna el numero antes de incluir este lote en un Packing Slip.";
            return;
        }

        $this->errorMessage = null;

        if (in_array($lotId, $this->selectedLotIds)) {
            $this->selectedLotIds = array_values(array_diff($this->selectedLotIds, [$lotId]));
            unset($this->labelSpecs[$lotId]);
        } else {
            $this->selectedLotIds[] = $lotId;
            $this->labelSpecs[$lotId] = ''; // Campo de label_spec vacio por defecto
        }
    }

    /**
     * Limpia toda la seleccion actual.
     */
    public function clearSelection(): void
    {
        $this->selectedLotIds = [];
        $this->labelSpecs = [];
        $this->errorMessage = null;
    }

    // =========================================================
    // Modal de creacion de Packing Slip
    // =========================================================

    /**
     * Abre el modal de confirmacion para crear el PS.
     * Requiere al menos un lote seleccionado.
     */
    public function openCreatePsModal(): void
    {
        if (empty($this->selectedLotIds)) {
            $this->errorMessage = 'Debes seleccionar al menos un lote para crear un Packing Slip.';
            return;
        }

        $this->errorMessage = null;
        $this->psNotes = '';
        $this->showCreatePsModal = true;
    }

    /**
     * Cancela la creacion del PS y cierra el modal.
     */
    public function cancelCreatePs(): void
    {
        $this->showCreatePsModal = false;
        $this->psNotes = '';
    }

    /**
     * Crea el Packing Slip con los lotes seleccionados.
     *
     * Proceso:
     * 1. Valida que los lotes existen y siguen en la cola (no asignados a otro PS).
     * 2. Valida que todos los WOs tienen external_wo_number.
     * 3. Crea el PackingSlip en estado 'pending'.
     * 4. Crea un PackingSlipItem por cada lote con los snapshots correspondientes.
     * 5. NO marca el lote como "fuera de la cola" — el scope scopeReadyForShipping
     *    usa whereDoesntHave('packingSlipItem'), por lo que al existir el item,
     *    el lote sale automaticamente de la cola.
     */
    public function createPackingSlip(): void
    {
        if (empty($this->selectedLotIds)) {
            $this->errorMessage = 'No hay lotes seleccionados.';
            return;
        }

        // Validar label_specs (longitud maxima 50 chars, decision D-06-02)
        foreach ($this->labelSpecs as $lotId => $spec) {
            if (strlen($spec) > 50) {
                $this->errorMessage = "El campo Label Spec del lote #{$lotId} excede 50 caracteres.";
                return;
            }
        }

        // Cargar lotes con sus relaciones necesarias
        $lots = Lot::with(['workOrder', 'packagingRecords'])
            ->whereIn('id', $this->selectedLotIds)
            ->where('ready_for_shipping', true)
            ->whereDoesntHave('packingSlipItem')
            ->get();

        // Verificar que todos los lotes seleccionados siguen en la cola
        if ($lots->count() !== count($this->selectedLotIds)) {
            $foundIds = $lots->pluck('id')->toArray();
            $missingIds = array_diff($this->selectedLotIds, $foundIds);
            $this->errorMessage = "Algunos lotes ya no estan disponibles en la cola (IDs: " . implode(', ', $missingIds) . "). Por favor recarga la pagina.";
            return;
        }

        // Validar que todos los WOs tienen external_wo_number (decision D-06-05)
        $lotsWithoutExternalWo = $lots->filter(fn ($lot) => !$lot->workOrder->hasExternalWoNumber());
        if ($lotsWithoutExternalWo->isNotEmpty()) {
            $numbers = $lotsWithoutExternalWo->pluck('lot_number')->implode(', ');
            $this->errorMessage = "Los siguientes lotes no pueden incluirse porque su WO no tiene numero externo: {$numbers}. Edita las WOs y asigna el numero externo antes de crear el Packing Slip.";
            return;
        }

        DB::beginTransaction();
        try {
            // Crear el Packing Slip en estado pending
            $packingSlip = PackingSlip::create([
                'created_by' => Auth::id(),
                'status'     => PackingSlip::STATUS_PENDING,
                'notes'      => $this->psNotes ?: null,
            ]);

            // Crear un item por cada lote con sus snapshots
            foreach ($lots as $lot) {
                $wo = $lot->workOrder;

                // Construir el codigo de WO para FPL-10 usando el lot_number como lot_seq
                // El lot_number puede ser '001', '002', etc. Convertimos a entero para buildWoCode.
                $lotSeq = (int) $lot->lot_number;
                $woNumberPs = $wo->buildWoCode($lotSeq);

                // Snapshot del date code: usar lot_number como valor provisional (decision D-06-01)
                // TODO(P-06-01): Confirmar con S.E.I.P., Inc. el dato exacto esperado en columna G del FPL-10
                //   antes de lanzar el PDF a produccion.
                $lotDateCode = $lot->lot_number;

                PackingSlipItem::create([
                    'packing_slip_id' => $packingSlip->id,
                    'lot_id'          => $lot->id,
                    'quantity_packed' => $lot->quantity_packed_final ?? $lot->getPackagingPackedPieces(),
                    'wo_number_ps'    => $woNumberPs,
                    'lot_date_code'   => $lotDateCode,
                    'label_spec'      => $this->labelSpecs[$lot->id] ?? null,
                    // Campos de Invoice (Fase 3): se dejan NULL
                    'unit_price'      => null,
                    'price_tier_id'   => null,
                    'price_source'    => null,
                ]);
            }

            DB::commit();

            Log::info('ShippingQueue: Packing Slip creado.', [
                'ps_id'     => $packingSlip->id,
                'ps_number' => $packingSlip->ps_number,
                'lots'      => $lots->pluck('lot_number')->toArray(),
                'user_id'   => Auth::id(),
            ]);

            $this->successMessage = "Packing Slip {$packingSlip->ps_number} creado exitosamente en estado Borrador.";
            $this->showCreatePsModal = false;
            $this->selectedLotIds = [];
            $this->labelSpecs = [];
            $this->psNotes = '';
            $this->errorMessage = null;

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('ShippingQueue: Error al crear Packing Slip.', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
                'lot_ids' => $this->selectedLotIds,
            ]);

            $this->errorMessage = 'Error al crear el Packing Slip: ' . $e->getMessage();
        }
    }

    // =========================================================
    // Render
    // =========================================================

    public function render()
    {
        // Cola principal: lotes listos para shipping, sin PS asignado
        $query = Lot::readyForShipping()
            ->with([
                'workOrder.purchaseOrder.part',
            ]);

        // Filtro de busqueda por lot_number o numero de parte
        if (!empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('lot_number', 'like', "%{$this->searchTerm}%")
                  ->orWhereHas('workOrder.purchaseOrder.part', function ($pq) {
                      $pq->where('number', 'like', "%{$this->searchTerm}%")
                         ->orWhere('description', 'like', "%{$this->searchTerm}%");
                  })
                  ->orWhereHas('workOrder', function ($wq) {
                      $wq->where('external_wo_number', 'like', "%{$this->searchTerm}%");
                  });
            });
        }

        // Filtro por tipo de cierre
        if (!empty($this->filterClosedByType)) {
            $query->where('closed_by_type', $this->filterClosedByType);
        }

        $lotsInQueue = $query->orderBy('ready_for_shipping_at', 'desc')->paginate(25);

        // Datos de los lotes seleccionados para el modal de confirmacion
        $selectedLots = [];
        if (!empty($this->selectedLotIds)) {
            $selectedLots = Lot::with(['workOrder.purchaseOrder.part'])
                ->whereIn('id', $this->selectedLotIds)
                ->get();
        }

        // Determinar si el usuario puede crear PS (Admin o Shipping)
        // TODO(D-06-04): Refinar con Spatie Permissions cuando esten definidos los permisos del PS
        $canCreatePs = Auth::check(); // Placeholder: cualquier autenticado puede crear

        return view('livewire.admin.shipping.shipping-queue', [
            'lotsInQueue'  => $lotsInQueue,
            'selectedLots' => $selectedLots,
            'canCreatePs'  => $canCreatePs,
            'closureTypes' => [
                ''               => 'Todos los tipos',
                'complete_lot'   => 'Lote completo',
                'new_lot'        => 'Nuevo lote',
                'close_as_is'    => 'Cerrado tal cual',
            ],
        ]);
    }
}
