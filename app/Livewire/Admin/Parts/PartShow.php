<?php

namespace App\Livewire\Admin\Parts;

use App\Models\Part;
use Livewire\Component;

class PartShow extends Component
{
    public Part $part;

    public function mount(Part $part): void
    {
        $this->part = $part->load('prices');
    }

    public function render()
    {
        return view('livewire.admin.parts.part-show');
    }
}
