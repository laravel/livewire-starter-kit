<?php

namespace App\Livewire\Admin\Holidays;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Holiday;

class Holidays extends Component
{
    use WithPagination;
    public $search = '';
    public $sortField = 'date';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $deleteId = null;
    public $confirmingDeletion = false;

    public function mount()
    {
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function confirmDeletion($id)
    {
        $this->deleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        $holiday = Holiday::findOrFail($this->deleteId);
        $holiday->delete();

        session()->flash('flash.banner', 'Holiday eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->confirmingDeletion = false;
    }

    public function render(): mixed
    {
        $holidays = Holiday::search($this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.holidays.holiday-list', [
            'holidays' => $holidays,
        ]);
    }
}; 
