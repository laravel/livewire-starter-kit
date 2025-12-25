<?php

namespace App\Livewire\Admin\ProductionStatuses;

use App\Models\ProductionStatus;
use Livewire\Component;

class ProductionStatusShow extends Component
{
    public ProductionStatus $productionStatus;

    public function mount(ProductionStatus $productionStatus): void
    {
        $this->productionStatus = $productionStatus->load(['tables', 'semiAutomatics', 'machines']);
    }

    public function render()
    {
        return view('livewire.admin.production-statuses.production-status-show');
    }
}
