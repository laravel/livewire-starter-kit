<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Table;
use Livewire\Component;

class TableShow extends Component
{
    public Table $table;

    public function mount(Table $table): void
    {
        $this->table = $table->load(['area', 'productionStatus', 'standard']);
    }

    public function render()
    {
        return view('livewire.admin.tables.table-show');
    }
}
