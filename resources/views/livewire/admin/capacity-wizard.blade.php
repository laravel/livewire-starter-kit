<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Wizard de capacidad</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Calcula la capacidad de producción en 4 pasos</p>
        </div>
    </div>

    {{-- Step indicator --}}
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <div class="flex items-center justify-center flex-wrap gap-2 sm:gap-0">
            @foreach([1 => 'Disponibilidad', 2 => 'Cálculo', 3 => 'Lotes', 4 => 'Fechas'] as $step => $label)
                <div class="flex items-center">
                    <button
                        wire:click="goToStep({{ $step }})"
                        @class([
                            'flex items-center justify-center w-10 h-10 rounded-full border-2 font-semibold transition',
                            'bg-blue-600 border-blue-600 text-white hover:bg-blue-700' => $currentStep >= $step,
                            'border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500' => $currentStep < $step,
                            'cursor-pointer' => $currentStep >= $step,
                            'cursor-not-allowed' => $currentStep < $step,
                        ])
                        @disabled($currentStep < $step)
                    >
                        {{ $step }}
                    </button>
                    <span @class([
                        'ml-2 text-sm font-medium hidden sm:inline',
                        'text-blue-600 dark:text-blue-400' => $currentStep >= $step,
                        'text-gray-400 dark:text-gray-500' => $currentStep < $step,
                    ])>{{ $label }}</span>
                </div>
                @if($step < 4)
                    <div @class([
                        'w-8 sm:w-16 h-1 mx-2 sm:mx-4 rounded flex-shrink-0',
                        'bg-blue-600' => $currentStep > $step,
                        'bg-gray-200 dark:bg-gray-700' => $currentStep <= $step,
                    ])></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Messages --}}
    @if($errorMessage)
        <div class="rounded-lg border-2 border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4">
            <p class="text-sm text-red-700 dark:text-red-300">{{ $errorMessage }}</p>
        </div>
    @endif

    @if($successMessage)
        <div class="rounded-lg border-2 border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4">
            <p class="text-sm text-green-700 dark:text-green-300">{{ $successMessage }}</p>
        </div>
    @endif

    @if(!empty($warnings))
        <div class="rounded-lg border-2 border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="flex-1">
                    <p class="font-medium text-amber-800 dark:text-amber-200 mb-1">Advertencias de capacidad</p>
                    <ul class="list-disc list-inside space-y-1 text-sm text-amber-700 dark:text-amber-300">
                        @foreach($warnings as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Step content --}}
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden p-6">
        @if($currentStep === 1)
            @include('livewire.admin.capacity-wizard.step1')
        @elseif($currentStep === 2)
            @include('livewire.admin.capacity-wizard.step2')
        @elseif($currentStep === 3)
            @include('livewire.admin.capacity-wizard.step3')
        @elseif($currentStep === 4)
            @include('livewire.admin.capacity-wizard.step4')
        @endif
    </div>
</div>
