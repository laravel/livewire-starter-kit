<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.employee')] class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cambiar Contraseña</h1>
        <p class="text-gray-600 dark:text-gray-400">Asegúrate de usar una contraseña segura</p>
    </div>

    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
        <form wire:submit="updatePassword" class="space-y-6">
            <flux:input 
                wire:model="current_password" 
                label="Contraseña Actual" 
                type="password" 
                required 
                autocomplete="current-password"
                placeholder="••••••••"
            />

            <flux:input 
                wire:model="password" 
                label="Nueva Contraseña" 
                type="password" 
                required 
                autocomplete="new-password"
                placeholder="••••••••"
            />

            <flux:input 
                wire:model="password_confirmation" 
                label="Confirmar Contraseña" 
                type="password" 
                required 
                autocomplete="new-password"
                placeholder="••••••••"
            />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">Actualizar Contraseña</flux:button>

                <x-action-message class="me-3" on="password-updated">
                    Contraseña actualizada.
                </x-action-message>
            </div>
        </form>
    </div>
</div>
