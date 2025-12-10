<?php

use Livewire\Volt\Component;
use App\Models\Semi_Automatic;
use App\Models\Area;

new class extends Component {
    public Semi_Automatic $semiAutomatic;
    public $number = '';
    public $employees = '';
    public $active = true;
    public $comments = '';
    public $area_id = '';
    public $areas = [];

    public function mount(Semi_Automatic $semiAutomatic)
    {
        $this->semiAutomatic = $semiAutomatic;
        $this->number = $semiAutomatic->number;
        $this->employees = $semiAutomatic->employees;
        $this->active = $semiAutomatic->active;
        $this->comments = $semiAutomatic->comments;
        $this->area_id = $semiAutomatic->area_id;
        $this->areas = Area::orderBy('name')->get();
    }

    public function rules()
    {
        return [
            'number' => 'required|string|max:255',
            'employees' => 'nullable|integer|min:1',
            'active' => 'boolean',
            'comments' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
        ];
    }

    public function save()
    {
        $this->validate();

        $this->semiAutomatic->update([
            'number' => $this->number,
            'employees' => $this->employees,
            'active' => $this->active,
            'comments' => $this->comments,
            'area_id' => $this->area_id,
        ]);

        session()->flash('flash.banner', 'Semi-automático actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('semi-automatics.index');
    }

    public function render(): mixed
    {
        return view('livewire.semi-automatics.semi-automatic-edit');
    }
}; ?>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('semi-automatics.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Semi-automático</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Modifica la información del semi-automático {{ $semiAutomatic->number }}
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
                            <label for="number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Número <span class="text-red-500">*</span>
                            </label>
                            <input 
                                wire:model="number" 
                                type="text" 
                                id="number" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Número del semi-automático"
                            >
                            @error('number')
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

                        <div class="flex items-center">
                            <label class="flex items-center">
                                <input 
                                    wire:model="active" 
                                    type="checkbox" 
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Semi-automático activo</span>
                            </label>
                            @error('active')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Comments -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Comentarios</h3>
                    <div>
                        <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Comentarios adicionales
                        </label>
                        <textarea 
                            wire:model="comments" 
                            id="comments" 
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Comentarios adicionales sobre el semi-automático..."
                        ></textarea>
                        @error('comments')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Actions -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('semi-automatics.index') }}" 
                           class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </a>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Actualizar Semi-automático
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>