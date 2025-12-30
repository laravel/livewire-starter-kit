<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Restaurant;

class MenuPage extends Component
{
    public Restaurant $restaurant;
    public string $search = '';

    public function mount(): void
    {
        // hardcoded restoran za /charlie
        $this->restaurant = Restaurant::where('slug', 'charlie')->firstOrFail();
    }

    public function render()
    {
        $categories = $this->restaurant
            ->categories()
            ->with(['items' => function ($query) {
                $query->when(trim($this->search) !== '', function ($q) {
                    $q->where('name', 'like', '%' . trim($this->search) . '%');
                });
            }])
            ->get()
            ->filter(fn ($category) => $category->items->isNotEmpty());

        return view('livewire.menu-page', [
            'restaurant' => $this->restaurant,
            'categories' => $categories,
        ])->layout('components.layouts.menu');
    }
}
