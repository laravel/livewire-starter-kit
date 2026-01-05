<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.employee')] class extends Component {
    public string $name = '';
    public string $last_name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->last_name = Auth::user()->last_name ?? '';
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('employee.dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Configuración de Perfil</h1>
        <p class="text-gray-600 dark:text-gray-400">Actualiza tu nombre y correo electrónico</p>
    </div>

    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
        <form wire:submit="updateProfileInformation" class="space-y-6">
            <flux:input wire:model="name" label="Nombre" type="text" required autofocus autocomplete="name" />
            
            <flux:input wire:model="last_name" label="Apellido" type="text" required autocomplete="family-name" />

            <div>
                <flux:input wire:model="email" label="Correo Electrónico" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                    <div class="mt-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Tu correo electrónico no está verificado.
                            <button type="button" wire:click.prevent="resendVerificationNotification" class="text-blue-600 hover:underline">
                                Haz clic aquí para reenviar el correo de verificación.
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                                Se ha enviado un nuevo enlace de verificación a tu correo.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">Guardar</flux:button>

                <x-action-message class="me-3" on="profile-updated">
                    Guardado.
                </x-action-message>
            </div>
        </form>
    </div>
</div>
