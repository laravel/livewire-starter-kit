<?php

namespace App\Livewire\Admin\Materials;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Lot;
use App\Models\WorkOrder;
use App\Services\AuditTrailService;
use Illuminate\Support\Facades\Auth;

class LotManagement extends Component
{
    use WithPagination;

    public ?int $workOrderId = null;
    public ?int $lotId = null;
    public ?Lot $lot = null;
    public bool $showModal = false;
    public string $searchTerm = '';
    public string $filterStatus = '';
    public ?int $filterWorkOrderId = null;

    public array $form = [
        'work_order_id' => null,
        'lot_number' => '',
        'description' => '',
        'quantity' => '',
        'status' => 'pending',
        'comments' => '',
        'raw_material_batch_numbers' => [''],
        'supplier_name' => '',
        'receipt_date' => '',
        'expiration_date' => '',
    ];

    protected AuditTrailService $auditTrailService;

    /**
     * Boot the component.
     */
    public function boot(AuditTrailService $auditTrailService): void
    {
        $this->auditTrailService = $auditTrailService;
    }

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        // No dependencies required
    }

    /**
     * Reset pagination when filters change.
     */
    public function updatedSearchTerm(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterWorkOrderId(): void
    {
        $this->resetPage();
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->searchTerm = '';
        $this->filterStatus = '';
        $this->filterWorkOrderId = null;
        $this->resetPage();
    }

    /**
     * Open modal to create a new lot.
     */
    public function openCreateModal(int $workOrderId): void
    {
        $this->resetForm();
        $this->workOrderId = $workOrderId;
        $this->form['work_order_id'] = $workOrderId;
        $this->showModal = true;
    }

    /**
     * Open modal to edit an existing lot.
     */
    public function openEditModal(int $lotId): void
    {
        $this->lot = Lot::findOrFail($lotId);
        $this->lotId = $lotId;
        
        $this->form = [
            'work_order_id' => $this->lot->work_order_id,
            'lot_number' => $this->lot->lot_number,
            'description' => $this->lot->description ?? '',
            'quantity' => $this->lot->quantity,
            'status' => $this->lot->status,
            'comments' => $this->lot->comments ?? '',
            'raw_material_batch_numbers' => $this->lot->raw_material_batch_numbers ?? [''],
            'supplier_name' => $this->lot->supplier_name ?? '',
            'receipt_date' => $this->lot->receipt_date?->format('Y-m-d') ?? '',
            'expiration_date' => $this->lot->expiration_date?->format('Y-m-d') ?? '',
        ];

        $this->showModal = true;
    }

    /**
     * Add a new batch number field.
     */
    public function addBatchNumber(): void
    {
        $this->form['raw_material_batch_numbers'][] = '';
    }

    /**
     * Remove a batch number field.
     */
    public function removeBatchNumber(int $index): void
    {
        unset($this->form['raw_material_batch_numbers'][$index]);
        $this->form['raw_material_batch_numbers'] = array_values($this->form['raw_material_batch_numbers']);
    }

    /**
     * Validate ISO traceability fields.
     */
    protected function validateISOFields(): void
    {
        $this->validate([
            'form.raw_material_batch_numbers' => 'required|array|min:1',
            'form.raw_material_batch_numbers.*' => 'required|string|max:255',
            'form.supplier_name' => 'required|string|max:255',
            'form.receipt_date' => 'required|date|before_or_equal:today',
            'form.expiration_date' => 'nullable|date|after:form.receipt_date',
        ], [
            'form.raw_material_batch_numbers.required' => 'Debe agregar al menos un número de lote de materia prima.',
            'form.raw_material_batch_numbers.*.required' => 'El número de lote no puede estar vacío.',
            'form.supplier_name.required' => 'El nombre del proveedor es obligatorio.',
            'form.receipt_date.required' => 'La fecha de recepción es obligatoria.',
            'form.receipt_date.before_or_equal' => 'La fecha de recepción no puede ser futura.',
            'form.expiration_date.after' => 'La fecha de expiración debe ser posterior a la fecha de recepción.',
        ]);
    }

    /**
     * Save the lot (create or update).
     */
    public function saveLot(): void
    {
        // Validate ISO fields
        $this->validateISOFields();

        // Validate other fields
        $this->validate([
            'form.work_order_id' => 'required|exists:work_orders,id',
            'form.quantity' => 'required|integer|min:1',
            'form.status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        // Filter empty batch numbers
        $this->form['raw_material_batch_numbers'] = array_filter($this->form['raw_material_batch_numbers']);

        // Validar que la suma de lotes no sobrepase la Cant. WO
        $workOrder = \App\Models\WorkOrder::find($this->form['work_order_id']);
        if ($workOrder) {
            $excludeId = $this->lotId ?? 0;
            $otherLotsTotal = $workOrder->lots()->where('id', '!=', $excludeId)->sum('quantity');
            if (($otherLotsTotal + $this->form['quantity']) > $workOrder->original_quantity) {
                $available = $workOrder->original_quantity - $otherLotsTotal;
                $this->addError('form.quantity', 'La suma de lotes sobrepasaría la Cant. WO (' . number_format($workOrder->original_quantity) . '). Máximo disponible: ' . number_format($available));
                return;
            }
        }

        if ($this->lotId) {
            // Update existing lot
            $oldValues = $this->lot->toArray();
            $this->lot->update($this->form);
            
            // Record audit trail
            $this->auditTrailService->recordUpdate(
                $this->lot->fresh(),
                Auth::user(),
                $oldValues,
                $this->form
            );

            session()->flash('message', 'Lote actualizado exitosamente.');
        } else {
            // Create new lot
            $lot = Lot::create($this->form);
            
            // Record audit trail
            $this->auditTrailService->recordCreate($lot, Auth::user());

            session()->flash('message', 'Lote creado exitosamente.');
        }

        $this->closeModal();
        $this->dispatch('lot-saved');
    }

    /**
     * Delete a lot.
     */
    public function deleteLot(int $lotId): void
    {
        $lot = Lot::findOrFail($lotId);

        if (!$lot->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este lote porque está asociado a kits.');
            return;
        }

        // Record audit trail before deletion
        $this->auditTrailService->recordDelete($lot, Auth::user());

        $lot->delete();

        session()->flash('message', 'Lote eliminado exitosamente.');
        $this->dispatch('lot-deleted');
    }

    /**
     * Close the modal.
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    /**
     * Reset the form.
     */
    protected function resetForm(): void
    {
        $this->lotId = null;
        $this->lot = null;
        $this->form = [
            'work_order_id' => null,
            'lot_number' => '',
            'description' => '',
            'quantity' => '',
            'status' => 'pending',
            'comments' => '',
            'raw_material_batch_numbers' => [''],
            'supplier_name' => '',
            'receipt_date' => '',
            'expiration_date' => '',
        ];
        $this->resetValidation();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        // Get all Work Orders that have lots for the dropdown
        $workOrders = WorkOrder::whereHas('lots')
            ->with(['purchaseOrder.part'])
            ->orderBy('wo_number', 'desc')
            ->get();

        // Build query for lots
        $query = Lot::with(['workOrder.purchaseOrder.part', 'workOrder.purchaseOrder.sentLists']);

        // Apply search filter
        if (!empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('lot_number', 'like', "%{$this->searchTerm}%")
                    ->orWhere('supplier_name', 'like', "%{$this->searchTerm}%")
                    ->orWhereHas('workOrder', function ($woQuery) {
                        $woQuery->where('wo_number', 'like', "%{$this->searchTerm}%");
                    })
                    ->orWhereHas('workOrder.purchaseOrder.part', function ($partQuery) {
                        $partQuery->where('number', 'like', "%{$this->searchTerm}%")
                            ->orWhere('description', 'like', "%{$this->searchTerm}%");
                    });
            });
        }

        // Apply status filter
        if (!empty($this->filterStatus)) {
            $query->where('status', $this->filterStatus);
        }

        // Apply Work Order filter
        if (!empty($this->filterWorkOrderId)) {
            $query->where('work_order_id', $this->filterWorkOrderId);
        }

        $lots = $query->latest()->paginate(15);

        // Get all Work Orders for creating new lots (those that may not have lots yet)
        $allWorkOrders = WorkOrder::with(['purchaseOrder.part'])
            ->orderBy('wo_number', 'desc')
            ->limit(50)
            ->get();

        return view('livewire.admin.materials.lot-management', [
            'lots' => $lots,
            'workOrders' => $workOrders,
            'allWorkOrders' => $allWorkOrders,
        ]);
    }
}
