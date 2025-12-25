<?php

namespace App\Livewire\Admin\OverTimes;

use App\Models\OverTime;
use App\Models\Shift;
use Livewire\Component;
use Livewire\WithPagination;

class OverTimeList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'date';
    public string $sortDirection = 'desc';
    public string $filterShift = '';
    public bool $showDeleteModal = false;
    public ?int $overTimeToDelete = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterShift(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function confirmDelete(int $overTimeId): void
    {
        $this->overTimeToDelete = $overTimeId;
        $this->showDeleteModal = true;
    }

    public function deleteOverTime(): void
    {
        if ($this->overTimeToDelete) {
            OverTime::find($this->overTimeToDelete)->delete();
            session()->flash('flash.banner', 'Over Time eliminado correctamente.');
            session()->flash('flash.bannerStyle', 'success');
        }
        $this->showDeleteModal = false;
        $this->overTimeToDelete = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->overTimeToDelete = null;
    }

    public function render()
    {
        $shifts = Shift::orderBy('name')->get();

        $query = OverTime::with('shift')
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->filterShift, function ($query) {
                $query->byShift($this->filterShift);
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        $overTimes = $query->paginate(10);

        // Calculate statistics
        $stats = [
            'total' => OverTime::count(),
            'total_hours' => round(OverTime::all()->sum('total_hours'), 2),
            'upcoming' => OverTime::active()->count(),
        ];

        return view('livewire.admin.over-times.over-time-list', compact('overTimes', 'shifts', 'stats'));
    }
}
