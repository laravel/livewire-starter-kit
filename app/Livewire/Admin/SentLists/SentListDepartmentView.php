<?php

namespace App\Livewire\Admin\SentLists;

use App\Models\SentList;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SentListDepartmentView extends Component
{
    public SentList $sentList;
    public string $currentUserDepartment = '';
    public bool $canEdit = false;
    public bool $showApprovalModal = false;
    public string $approvalNotes = '';
    
    // Editable fields per PO
    public array $lotNumbers = [];
    public array $quantities = [];
    public string $generalNotes = '';

    public function mount(SentList $sentList)
    {
        $this->sentList = $sentList;
        $this->currentUserDepartment = $this->getUserDepartment();
        $this->canEdit = $this->sentList->canDepartmentEdit($this->currentUserDepartment);
        $this->generalNotes = $this->sentList->notes ?? '';
        
        // Load existing data
        foreach ($this->sentList->purchaseOrders as $po) {
            $this->lotNumbers[$po->id] = $po->pivot->lot_number ?? '';
            $this->quantities[$po->id] = $po->pivot->quantity ?? $po->quantity;
        }
    }

    protected function getUserDepartment(): string
    {
        $user = Auth::user();
        
        // Determinar el departamento basado en roles o permisos
        // Por ahora, asumimos que el usuario tiene un role que coincide con el departamento
        if ($user->hasRole('materials') || $user->hasRole('admin')) {
            return SentList::DEPT_MATERIALS;
        }
        if ($user->hasRole('production')) {
            return SentList::DEPT_PRODUCTION;
        }
        if ($user->hasRole('quality')) {
            return SentList::DEPT_QUALITY;
        }
        if ($user->hasRole('shipping')) {
            return SentList::DEPT_SHIPPING;
        }
        
        return SentList::DEPT_MATERIALS; // Default
    }

    public function updateLotNumber(int $poId, string $lotNumber)
    {
        if (!$this->canEdit) {
            $this->dispatch('error', 'No tiene permisos para editar esta lista.');
            return;
        }

        $this->lotNumbers[$poId] = $lotNumber;
    }

    public function saveChanges()
    {
        if (!$this->canEdit) {
            $this->dispatch('error', 'No tiene permisos para editar esta lista.');
            return;
        }

        try {
            // Update lot numbers and quantities for each PO
            foreach ($this->sentList->purchaseOrders as $po) {
                $this->sentList->purchaseOrders()->updateExistingPivot($po->id, [
                    'lot_number' => $this->lotNumbers[$po->id] ?? null,
                    'quantity' => $this->quantities[$po->id] ?? $po->quantity,
                ]);
            }

            // Update general notes
            $this->sentList->update([
                'notes' => $this->generalNotes,
            ]);

            $this->dispatch('success', 'Cambios guardados exitosamente.');
        } catch (\Exception $e) {
            $this->dispatch('error', 'Error al guardar cambios: ' . $e->getMessage());
        }
    }

    public function openApprovalModal()
    {
        if (!$this->canEdit) {
            $this->dispatch('error', 'No tiene permisos para aprobar esta lista.');
            return;
        }

        $this->showApprovalModal = true;
    }

    public function closeApprovalModal()
    {
        $this->showApprovalModal = false;
        $this->approvalNotes = '';
    }

    public function approveAndMoveToNextDepartment()
    {
        if (!$this->canEdit) {
            $this->dispatch('error', 'No tiene permisos para aprobar esta lista.');
            return;
        }

        try {
            // Save any pending changes first
            $this->saveChanges();

            // Add approval notes to general notes if provided
            if (!empty($this->approvalNotes)) {
                $currentNotes = $this->sentList->notes ?? '';
                $timestamp = now()->format('d/m/Y H:i');
                $userName = Auth::user()->name;
                $deptLabel = $this->sentList->getDepartmentLabelAttribute();
                
                $newNote = "\n[{$timestamp}] {$deptLabel} - {$userName}: {$this->approvalNotes}";
                $this->sentList->update([
                    'notes' => $currentNotes . $newNote,
                ]);
            }

            // Move to next department
            $moved = $this->sentList->moveToNextDepartment(Auth::id());

            if ($moved) {
                $this->closeApprovalModal();
                $this->dispatch('success', 'Lista aprobada y enviada al siguiente departamento.');
                
                // Redirect to list
                return redirect()->route('admin.sent-lists.index')
                    ->with('success', 'Lista aprobada exitosamente.');
            } else {
                // Already at last department - confirm the list
                $this->sentList->update(['status' => SentList::STATUS_CONFIRMED]);
                $this->closeApprovalModal();
                
                return redirect()->route('admin.sent-lists.index')
                    ->with('success', 'Lista confirmada exitosamente. Completó todo el flujo de departamentos.');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', 'Error al aprobar: ' . $e->getMessage());
        }
    }

    protected function isPastDepartment(string $department): bool
    {
        $order = [
            SentList::DEPT_MATERIALS => 1,
            SentList::DEPT_PRODUCTION => 2,
            SentList::DEPT_QUALITY => 3,
            SentList::DEPT_SHIPPING => 4,
        ];

        $currentOrder = $order[$this->sentList->current_department] ?? 0;
        $checkOrder = $order[$department] ?? 0;

        return $checkOrder < $currentOrder;
    }

    protected function isFutureDepartment(string $department): bool
    {
        $order = [
            SentList::DEPT_MATERIALS => 1,
            SentList::DEPT_PRODUCTION => 2,
            SentList::DEPT_QUALITY => 3,
            SentList::DEPT_SHIPPING => 4,
        ];

        $currentOrder = $order[$this->sentList->current_department] ?? 0;
        $checkOrder = $order[$department] ?? 0;

        return $checkOrder > $currentOrder;
    }

    public function render()
    {
        return view('livewire.admin.sent-lists.sent-list-department-view');
    }
}
