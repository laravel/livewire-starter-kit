<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.employee')] class extends Component {
    //
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Apariencia</h1>
        <p class="text-gray-600 dark:text-gray-400">Personaliza la apariencia de tu panel</p>
    </div>

    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tema</h2>
        
        <div class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Selecciona el tema de tu preferencia. El tema del sistema se ajustará automáticamente según la configuración de tu dispositivo.
            </p>

            <flux:radio.group x-data="{ theme: localStorage.getItem('theme') || 'system' }" x-init="$watch('theme', val => {
                localStorage.setItem('theme', val);
                if (val === 'dark' || (val === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })" x-model="theme" variant="segmented" class="w-full max-w-md">
                <flux:radio value="light" icon="sun">Claro</flux:radio>
                <flux:radio value="dark" icon="moon">Oscuro</flux:radio>
                <flux:radio value="system" icon="computer-desktop">Sistema</flux:radio>
            </flux:radio.group>
        </div>
    </div>
</div>
