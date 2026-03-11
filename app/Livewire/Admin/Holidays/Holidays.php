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

    public function updatingSearch(): void
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

    public function deleteHoliday(int $id): void
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();
        session()->flash('flash.banner', 'Día festivo eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function render(): mixed
    {
        $holidays = Holiday::search($this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $totalHolidays = Holiday::count();
        $upcomingHolidays = Holiday::where('date', '>=', now()->startOfDay())->count();
        $pastHolidays = Holiday::where('date', '<', now()->startOfDay())->count();

        return view('livewire.admin.holidays.holiday-list', [
            'holidays' => $holidays,
            'totalHolidays' => $totalHolidays,
            'upcomingHolidays' => $upcomingHolidays,
            'pastHolidays' => $pastHolidays,
        ]);
    }
} 
