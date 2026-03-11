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

        if (!$packingSlip->isDraft()) {
            session()->flash('error', 'Solo se pueden eliminar Packing Slips en estado Borrador.');
            return;
        }

        $this->deleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        $packingSlip = PackingSlip::findOrFail($this->deleteId);

        if (!$packingSlip->isDraft()) {
            session()->flash('error', 'Solo se pueden eliminar Packing Slips en estado Borrador.');
            $this->confirmingDeletion = false;
            return;
        }

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

        if ($this->filterStatus === 'draft') {
            $query->draft();
        } elseif ($this->filterStatus === 'confirmed') {
            $query->confirmed();
        } elseif ($this->filterStatus === 'shipped') {
            $query->shipped();
        }

        $packingSlips = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $stats = [
            'total'     => PackingSlip::count(),
            'draft'     => PackingSlip::draft()->count(),
            'confirmed' => PackingSlip::confirmed()->count(),
            'shipped'   => PackingSlip::shipped()->count(),
        ];

        return view('livewire.admin.packing-slips.packing-slip-list', [
            'packingSlips' => $packingSlips,
            'stats'        => $stats,
        ]);
    }
}
