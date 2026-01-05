<div class="p-6">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Wizard de Capacidad</h1>
        <p class="text-gray-600 dark:text-gray-400">Calcula la capacidad de producción en 3 pasos</p>
    </div>

    {{-- Step Indicator --}}
    <div class="mb-8">
        <div class="flex items-center justify-center">
            @foreach([1 => 'Disponibilidad', 2 => 'Cálculo', 3 => 'Cierre'] as $step => $label)
                <div class="flex items-center">
                    <button 
                        wire:click="goToStep({{ $step }})"
                        @class([
                            'flex items-center justify-center w-10 h-10 rounded-full border-2 font-semibold transition',
                            'bg-blue-600 border-blue-600 text-white' => $currentStep >= $step,
                            'border-gray-300 text-gray-400 dark:border-gray-600' => $currentStep < $step,
                            'cursor-pointer hover:bg-blue-700' => $currentStep >= $step,
                            'cursor-not-allowed' => $currentStep < $step,
                        ])
                        @disabled($currentStep < $step)
                    >
                        {{ $step }}
                    </button>
                    <span @class([
                        'ml-2 text-sm font-medium',
                        'text-blue-600 dark:text-blue-400' => $currentStep >= $step,
                        'text-gray-400' => $currentStep < $step,
                    ])>{{ $label }}</span>
                </div>
                @if($step < 3)
                    <div @class([
                        'w-16 h-1 mx-4 rounded',
                        'bg-blue-600' => $currentStep > $step,
                        'bg-gray-200 dark:bg-gray-700' => $currentStep <= $step,
                    ])></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Messages --}}
    @if($errorMessage)
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 p-4">
            <p class="text-sm text-red-700 dark:text-red-300">{{ $errorMessage }}</p>
        </div>
    @endif

    @if($successMessage)
        <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 p-4">
            <p class="text-sm text-green-700 dark:text-green-300">{{ $successMessage }}</p>
        </div>
    @endif

    {{-- Step Content --}}
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
        @if($currentStep === 1)
            @include('livewire.admin.capacity-wizard.step1')
        @elseif($currentStep === 2)
            @include('livewire.admin.capacity-wizard.step2')
        @elseif($currentStep === 3)
            @include('livewire.admin.capacity-wizard.step3')
        @endif
    </div>
</div>
