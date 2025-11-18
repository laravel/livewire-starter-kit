<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;

class PosList extends Component
{
    use WithPagination;

    public function render(): mixed
    {
        return view('livewire.pos.pos-list');
    }
} ?>

<div>

    <div class="mb-4 flex justify-between">
        <div class="w-1/3">
            <input wire:model.debounce.300ms="search" type="text" wire:model="search" placeholder="Buscar POS..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>
        <div>
            <select wire:model.live="perPage" name="perPage" id="perPage"
                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

</div>
