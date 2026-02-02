<?php

namespace App\Livewire\Admin\Materials;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Kit;
use App\Models\Lot;
use App\Models\WorkOrder;
use App\Services\AuditTrailService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KitManagement extends Component
{
    use WithPagination;

    public ?int $kitId = null;
    public ?Kit $kit = null;
    public bool $showModal = false;
    public bool $showApprovalHistory = false;
    public string $searchTerm = '';
    public string $filterStatus = '';
    public ?int $filterWorkOrderId = null;
    public array $selectedLots = [];

    public array $form = [
        'work_order_id' => null,
        'kit_number' => '',
        'status' => 'preparing',
        'validated' => false,
        'validation_notes' => '',
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
     * Select a lot for kit creation.
     */
    public function selectLot(int $lotId): void
    {
        if (!in_array($lotId, $this->selectedLots)) {
            $this->selectedLots[] = $lotId;
        }
    }

    /**
     * Deselect a lot.
     */
    public function deselectLot(int $lotId): void
    {
        $this->selectedLots = array_diff($this->selectedLots, [$lotId]);
    }

    /**
     * Toggle lot selection.
     */
    public function toggleLotSelection(int $lotId): void
    {
        if (in_array($lotId, $this->selectedLots)) {
            $this->deselectLot($lotId);
        } else {
            $this->selectLot($lotId);
        }
    }

    /**
     * Open modal to create a new kit.
     */
    public function openCreateModal(): void
    {
        if (empty($this->selectedLots)) {
            session()->flash('error', 'Debe seleccionar al menos un lote para crear un kit.');
            return;
        }

        // Get work order from first selected lot
        $firstLot = Lot::with('workOrder.purchaseOrder')->find($this->selectedLots[0]);
        if (!$firstLot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        // Save selected lots before resetting form
        $savedLots = $this->selectedLots;
        
        $this->resetForm();
        
        // Restore selected lots
        $this->selectedLots = $savedLots;
        $this->form['work_order_id'] = $firstLot->work_order_id;
        $this->form['kit_number'] = Kit::generateKitNumber($firstLot->work_order_id);
        $this->showModal = true;
    }

    /**
     * Open modal to edit an existing kit.
     */
    public function openEditModal(int $kitId): void
    {
        $this->kit = Kit::with('lots')->findOrFail($kitId);
        
        if (!$this->kit->canBeEdited()) {
            session()->flash('error', 'Este kit no puede ser editado en su estado actual.');
            return;
        }

        $this->kitId = $kitId;
        $this->selectedLots = $this->kit->lots->pluck('id')->toArray();
        
        $this->form = [
            'work_order_id' => $this->kit->work_order_id,
            'kit_number' => $this->kit->kit_number,
            'status' => $this->kit->status,
            'validated' => $this->kit->validated,
            'validation_notes' => $this->kit->validation_notes ?? '',
        ];

        $this->showModal = true;
    }

    /**
     * Save the kit (create or update).
     */
    public function saveKit(): void
    {
        $this->validate([
            'form.work_order_id' => 'required|exists:work_orders,id',
            'form.kit_number' => 'required|string|max:100|unique:kits,kit_number,' . ($this->kitId ?? 'NULL'),
            'selectedLots' => 'required|array|min:1',
            'selectedLots.*' => 'exists:lots,id',
        ], [
            'selectedLots.required' => 'Debe seleccionar al menos un lote.',
            'selectedLots.min' => 'Debe seleccionar al menos un lote.',
        ]);

        // Validate that no cancelled lots are selected
        $cancelledLots = Lot::whereIn('id', $this->selectedLots)
            ->where('status', Lot::STATUS_CANCELLED)
            ->count();

        if ($cancelledLots > 0) {
            session()->flash('error', 'No se pueden crear kits con lotes cancelados.');
            return;
        }

        DB::transaction(function () {
            if ($this->kitId) {
                // Update existing kit
                $oldValues = $this->kit->toArray();
                $this->kit->update($this->form);
                
                // Sync lots with created_at timestamp
                $syncData = [];
                foreach ($this->selectedLots as $lotId) {
                    $syncData[$lotId] = ['created_at' => now()];
                }
                $this->kit->lots()->sync($syncData);
                
                // Record audit trail
                $this->auditTrailService->recordUpdate(
                    $this->kit->fresh(),
                    Auth::user(),
                    $oldValues,
                    $this->form
                );

                session()->flash('message', 'Kit actualizado exitosamente.');
            } else {
                // Create new kit
                $this->form['prepared_by'] = Auth::id();
                $kit = Kit::create($this->form);
                
                // Attach lots with created_at timestamp
                $attachData = [];
                foreach ($this->selectedLots as $lotId) {
                    $attachData[$lotId] = ['created_at' => now()];
                }
                $kit->lots()->attach($attachData);
                
                // Record audit trail
                $this->auditTrailService->recordCreate($kit, Auth::user());

                session()->flash('message', 'Kit creado exitosamente.');
            }
        });

        $this->closeModal();
        $this->dispatch('kit-saved');
    }

    /**
     * Submit kit to Quality for approval.
     */
    public function submitToQuality(int $kitId): void
    {
        $kit = Kit::findOrFail($kitId);

        if ($kit->status !== Kit::STATUS_PREPARING) {
            session()->flash('error', 'Solo se pueden enviar kits en estado "En Preparación".');
            return;
        }

        DB::transaction(function () use ($kit) {
            $kit->submitToQuality(Auth::user());
            
            // Record audit trail
            $this->auditTrailService->recordStatusChange(
                $kit,
                Auth::user(),
                Kit::STATUS_PREPARING,
                Kit::STATUS_READY
            );
        });

        session()->flash('message', 'Kit enviado a Calidad para aprobación.');
        $this->dispatch('kit-submitted');
    }

    /**
     * Delete a kit.
     */
    public function deleteKit(int $kitId): void
    {
        $kit = Kit::findOrFail($kitId);

        if (!$kit->canBeDeleted()) {
            session()->flash('error', 'Este kit no puede ser eliminado en su estado actual.');
            return;
        }

        // Record audit trail before deletion
        $this->auditTrailService->recordDelete($kit, Auth::user());

        $kit->delete();

        session()->flash('message', 'Kit eliminado exitosamente.');
        $this->dispatch('kit-deleted');
    }

    /**
     * Show approval history for a kit.
     */
    public function showApprovalHistoryModal(int $kitId): void
    {
        // Close edit modal if open
        $this->showModal = false;
        $this->kitId = null;
        
        $this->kit = Kit::with('approvalCycles.submitter', 'approvalCycles.reviewer')->findOrFail($kitId);
        $this->showApprovalHistory = true;
    }

    /**
     * Close modals.
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showApprovalHistory = false;
        $this->resetForm();
    }

    /**
     * Reset the form.
     */
    protected function resetForm(): void
    {
        $this->kitId = null;
        $this->kit = null;
        $this->selectedLots = [];
        $this->form = [
            'work_order_id' => null,
            'kit_number' => '',
            'status' => 'preparing',
            'validated' => false,
            'validation_notes' => '',
        ];
        $this->resetValidation();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        // Get Work Orders that have kits for the filter dropdown
        $workOrders = WorkOrder::whereHas('kits')
            ->with(['purchaseOrder.part'])
            ->orderBy('id', 'desc')
            ->get();

        // Build query for kits
        $query = Kit::with(['workOrder.purchaseOrder.part', 'lots', 'preparedBy', 'approvalCycles']);

        // Apply search filter
        if (!empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('kit_number', 'like', "%{$this->searchTerm}%")
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

        $kits = $query->latest()->paginate(15);

        // Load available lots for kit creation (not cancelled and not already in a kit)
        $availableLots = Lot::where('status', '!=', Lot::STATUS_CANCELLED)
            ->whereDoesntHave('kits')
            ->with(['workOrder.purchaseOrder.part'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return view('livewire.admin.materials.kit-management', [
            'kits' => $kits,
            'availableLots' => $availableLots,
            'workOrders' => $workOrders,
        ]);
    }
}
