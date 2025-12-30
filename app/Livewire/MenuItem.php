<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Restaurant;
use Livewire\Component;

class MenuItem extends Component
{
    public Restaurant $restaurant;
    public Item $item;

    public function mount(Restaurant $restaurant, Item $item): void
    {
        $this->restaurant = $restaurant;
        $this->item = $item;
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        return view('livewire.menu-item')
            ->layout('components.layouts.menu');

    }
}
