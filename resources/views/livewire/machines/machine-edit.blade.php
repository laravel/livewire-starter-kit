<?php

use Livewire\Volt\Component;
use App\Models\Machine;
use App\Models\Area;

new class extends Component {
    public Machine $machine;
    public $name = '';
    public $brand = '';
    public $model = '';
    public $sn = '';
    public $asset_number = '';
    public $employees = '';
    public $setup_time = '';
    public $maintenance_time = '';
    public $active = true;
    public $comments = '';
    public $area_id = '';
    public $areas = [];

    public function mount(Machine $machine)
    {
        $this->machine = $machine;
        $this->name = $machine->name;
        $this->brand = $machine->brand;
        $this->model = $machine->model;
        $this->sn = $machine->sn;
        $this->asset_number = $machine->asset_number;
        $this->employees = $machine->employees;
        $this->setup_time = $machine->setup_time;
        $this->maintenance_time = $machine->maintenance_time;
        $this->active = $machine->active;
        $this->comments = $machine->comments;
        $this->area_id = $machine->area_id;
        $this->areas = Area::orderBy('name')->get();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'sn' => 'nullable|string|max:255',
            'asset_number' => 'nullable|string|max:255',
            'employees' => 'nullable|integer|min:1',
            'setup_time' => 'nullable|numeric|min:0',
            'maintenance_time' => 'nullable|numeric|min:0',
            'active' => 'boolean',
            'comments' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
        ];
    }

    public function save()
    {
        $this->validate();

        $this->machine->update([
            'name' => $this->name,
            'brand' => $this->brand,
            'model' => $this->model,
            'sn' => $this->sn,
            'asset_number' => $this->asset_number,
            'employees' => $this->employees,
            'setup_time' => $this->setup_time,
            'maintenance_time' => $this->maintenance_time,
            'active' => $this->active,
            'comments' => $this->comments,
            'area_id' => $this->area_id,
        ]);

        session()->flash('flash.banner', 'Máquina actualizada correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('machines.index');
    }

    public function render(): mixed
    {
        return view('livewire.machines.machine-edit');
    }
}; ?>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('machines.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Máquina</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Modifica la información de la máquina: {{ $machine->name }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <form wire:submit="save" class="p-6 space-y-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Información Básica</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input 
                                wire:model="name" 
                                type="text" 
                                id="name" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Nombre de la máquina"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Marca
                            </label>
                            <input 
                                wire:model="brand" 
                                type="text" 
                                id="brand" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Marca de la máquina"
                            >
                            @error('brand')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Modelo
                            </label>
                            <input 
                                wire:model="model" 
                                type="text" 
                                id="model" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Modelo de la máquina"
                            >
                            @error('model')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sn" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Número de Serie
                            </label>
                            <input 
                                wire:model="sn" 
                                type="text" 
                                id="sn" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Número de serie"
                            >
                            @error('sn')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="asset_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Número de Activo
                            </label>
                            <input 
                                wire:model="asset_number" 
                                type="text" 
                                id="asset_number" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Número de activo"
                            >
                            @error('asset_number')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="area_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Área <span class="text-red-500">*</span>
                            </label>
                            <select 
                                wire:model="area_id" 
                                id="area_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                                <option value="">Selecciona un área</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                            @error('area_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Operational Information -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Información Operacional</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="employees" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Número de Empleados
                            </label>
                            <input 
                                wire:model="employees" 
                                type="number" 
                                id="employees" 
                                min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Ej: 2"
                            >
                            @error('employees')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="setup_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tiempo de Setup (horas)
                            </label>
                            <input 
                                wire:model="setup_time" 
                                type="number" 
                                id="setup_time" 
                                step="0.01"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Ej: 1.5"
                            >
                            @error('setup_time')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="maintenance_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tiempo de Mantenimiento (horas)
                            </label>
                            <input 
                                wire:model="maintenance_time" 
                                type="number" 
                                id="maintenance_time" 
                                step="0.01"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Ej: 2.0"
                            >
                            @error('maintenance_time')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Status and Comments -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Estado y Comentarios</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="flex items-center">
                                <input 
                                    wire:model="active" 
                                    type="checkbox" 
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Máquina activa</span>
                            </label>
                            @error('active')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Comentarios
                            </label>
                            <textarea 
                                wire:model="comments" 
                                id="comments" 
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Comentarios adicionales sobre la máquina..."
                            ></textarea>
                            @error('comments')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('machines.index') }}" 
                           class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </a>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Actualizar Máquina
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>