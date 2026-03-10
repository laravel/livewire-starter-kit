<?php

namespace App\Livewire\Admin\PackingSlips;

use App\Models\PackingSlip;
use Livewire\Component;
use Livewire\WithPagination;

class PackingSlipList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = 'all';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public ?int $deleteId = null;
    public bool $confirmingDeletion = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function confirmDeletion(int $id): void
    {
        $packingSlip = PackingSlip::findOrFail($id);

        if (!$packingSlip->isPending()) {
            session()->flash('error', 'Solo se pueden eliminar Packing Slips en estado Pendiente.');
            return;
        }

        $this->deleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        $packingSlip = PackingSlip::findOrFail($this->deleteId);

        if (!$packingSlip->isPending()) {
            session()->flash('error', 'Solo se pueden eliminar Packing Slips en estado Pendiente.');
            $this->confirmingDeletion = false;
            return;
        }

        // Eliminar explicitamente los items antes del soft-delete del PS.
        // Esto libera los lotes (packing_slip_items.lot_id UNIQUE) para que
        // puedan ser asignados a un nuevo Packing Slip. El cascade de BD no
        // se activa con SoftDeletes ya que no es un DELETE SQL real.
        $packingSlip->items()->delete();

        $packingSlip->delete();

        session()->flash('flash.banner', 'Packing Slip eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->confirmingDeletion = false;
        $this->deleteId = null;
    }

    public function cancelDeletion(): void
    {
        $this->confirmingDeletion = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $query = PackingSlip::with(['creator', 'items'])
            ->search($this->search);

        if ($this->filterStatus === 'pending') {
            $query->pending();
        } elseif ($this->filterStatus === 'shipped') {
            $query->shipped();
        } elseif ($this->filterStatus === 'cancelled') {
            $query->cancelled();
        }

        $packingSlips = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $stats = [
            'total'     => PackingSlip::count(),
            'pending'   => PackingSlip::pending()->count(),
            'shipped'   => PackingSlip::shipped()->count(),
            'cancelled' => PackingSlip::cancelled()->count(),
        ];

        return view('livewire.admin.packing-slips.packing-slip-list', [
            'packingSlips' => $packingSlips,
            'stats'        => $stats,
        ]);
    }
}
