<?php

use Flux\Flux;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Symfony\Component\HttpFoundation\Response;

new class extends Component {
    #[Locked]
    public bool $twoFactorEnabled;

    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showModal = false;

    public bool $showVerificationStep = false;

    #[Validate('required|string|min:6|max:6', onUpdate: false)]
    public string $code = '';

    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        abort_unless(Features::enabled(Features::twoFactorAuthentication()), Response::HTTP_FORBIDDEN);

        if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication(auth()->user());
        }

        $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
    }

    public function enable(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $enableTwoFactorAuthentication(auth()->user());
        if (!$this->requiresConfirmation) {
            $this->twoFactorEnabled = true;
        }
        $this->loadTwoFactorData();
        $this->showModal = true;
    }

    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());
        $this->twoFactorEnabled = false;
    }

    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();
        $confirmTwoFactorAuthentication(auth()->user(), $this->code);
        $this->closeModal();
        $this->twoFactorEnabled = true;
    }

    public function getModalConfigProperty(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => __('Two-Factor Authentication Enabled'),
                'description' => __('Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'),
                'buttonText' => __('Close')
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify Authentication Code'),
                'description' => __('Enter the 6-digit code from your authenticator app'),
                'buttonText' => __('Continue')
            ];
        }

        return [
            'title' => __('Enable Two-Factor Authentication'),
            'description' => __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app'),
            'buttonText' => __('Continue')
        ];
    }

    public function handleNextAction(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;
            $this->resetErrorBag();
        } else {
            $this->closeModal();
        }
    }

    public function resetVerification(): void
    {
        $this->showVerificationStep = false;
        $this->code = '';
        $this->resetErrorBag();
    }

    public function closeModal(): void
    {
        $this->showVerificationStep = false;
        $this->code = '';
        $this->qrCodeSvg = '';
        $this->manualSetupKey = '';
        $this->resetErrorBag();
        $this->showModal = false;

        if (!$this->requiresConfirmation) {
            $this->twoFactorEnabled = true;
        }
    }

    private function loadTwoFactorData(): void
    {
        $user = auth()->user();

        $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
        $this->manualSetupKey = decrypt($user->two_factor_secret);
    }
} ?>

<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.layout :heading="__('Two Factor Authentication')"
                       :subheading="__('Manage your two-factor authentication settings')">
        <div class="flex flex-col w-full mx-auto text-sm space-y-6" wire:cloak>
            @if(!$twoFactorEnabled)
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <flux:badge color="red">{{ __('Disabled') }}</flux:badge>
                    </div>

                    <flux:text variant="subtle">
                        {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                    </flux:text>

                    <flux:button
                        variant="primary"
                        icon="shield-check"
                        icon:variant="outline"
                        wire:click="enable"
                    >
                        {{ __('Enable 2FA') }}
                    </flux:button>
                </div>
            @else
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <flux:badge color="green">{{ __('Enabled') }}</flux:badge>
                    </div>

                    <flux:text>
                        {{ __('With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                    </flux:text>

                    @if($twoFactorEnabled)
                        <livewire:settings.two-factor.recovery-codes :$requiresConfirmation/>
                    @endif
                    <div class="flex justify-start">
                        <flux:button
                            variant="danger"
                            icon="shield-exclamation"
                            icon:variant="outline"
                            wire:click="disable"
                        >{{ __('Disable 2FA') }}
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </x-settings.layout>

    <flux:modal
        name="two-factor-setup-modal"
        class="max-w-md min-w-md"
        @close="closeModal"
        wire:model="showModal"
    >
        <div class="space-y-6">
            <div class="flex flex-col items-center space-y-4">
                <div
                    class="p-0.5 w-auto rounded-full border border-stone-100 dark:border-stone-600 bg-white dark:bg-stone-800 shadow-sm">
                    <div
                        class="p-2.5 rounded-full border border-stone-200 dark:border-stone-600 overflow-hidden bg-stone-100 dark:bg-stone-200 relative">
                        <div
                            class="flex items-stretch absolute inset-0 w-full h-full divide-x [&>div]:flex-1 divide-stone-200 dark:divide-stone-300 justify-around opacity-50">
                            @for($i = 1; $i <= 5; $i++)
                                <div></div>
                            @endfor
                        </div>
                        <div
                            class="flex flex-col items-stretch absolute w-full h-full divide-y [&>div]:flex-1 inset-0 divide-stone-200 dark:divide-stone-300 justify-around opacity-50">
                            @for($i = 1; $i <= 5; $i++)
                                <div></div>
                            @endfor
                        </div>
                        <flux:icon.qr-code class="relative z-20"/>
                    </div>
                </div>
                <div class="text-center space-y-2">
                    <flux:heading size="lg">{{ $this->modalConfig['title'] }}</flux:heading>
                    <flux:text>{{ $this->modalConfig['description'] }}</flux:text>
                </div>
            </div>

            @if(!$showVerificationStep)
                <div class="space-y-6">
                    <div class="flex justify-center">
                        <div
                            class="border border-stone-200 dark:border-stone-700 rounded-lg relative overflow-hidden w-64 aspect-square">
                            @if(empty($qrCodeSvg))
                                <div
                                    class="bg-white dark:bg-stone-700 animate-pulse flex items-center justify-center absolute inset-0">
                                    <flux:icon.loading/>
                                </div>
                            @else
                                <div class="p-4 flex items-center justify-center h-full">
                                    {!! $qrCodeSvg !!}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <flux:button
                            variant="primary"
                            class="w-full"
                            wire:click="handleNextAction"
                        >
                            {{ $this->modalConfig['buttonText'] }}
                        </flux:button>
                    </div>

                    <div class="space-y-4">
                        <div class="relative flex w-full items-center justify-center">
                            <div
                                class="absolute inset-0 top-1/2 h-px w-full bg-stone-200 dark:bg-stone-600"></div>
                            <span
                                class="relative bg-white dark:bg-stone-800 px-2 text-sm text-stone-600 dark:text-stone-400">
                                {{ __('or, enter the code manually') }}
                            </span>
                        </div>

                        <div class="flex items-center space-x-2"
                             x-data="{
                                 copied: false,
                                 async copy() {
                                     try {
                                         await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                         this.copied = true;
                                         setTimeout(() => this.copied = false, 1500);
                                     } catch (e) {
                                         console.warn('Could not copy to clipboard');
                                     }
                                 }
                             }">
                            <div
                                class="w-full rounded-xl flex items-stretch border dark:border-stone-700">
                                @if(empty($manualSetupKey))
                                    <div
                                        class="w-full flex items-center justify-center bg-stone-100 dark:bg-stone-700 p-3">
                                        <flux:icon.loading variant="mini"/>
                                    </div>
                                @else
                                    <input
                                        type="text"
                                        readonly
                                        value="{{ $manualSetupKey }}"
                                        class="w-full p-3 bg-transparent text-stone-900 dark:text-stone-100 outline-none"
                                    />
                                    <button
                                        @click="copy()"
                                        class="border-l border-stone-200 dark:border-stone-600 px-3 hover:bg-stone-100 dark:hover:bg-stone-700 transition-colors"
                                    >
                                        <flux:icon.document-duplicate x-show="!copied"
                                                                      variant="outline"></flux:icon>
                                        <flux:icon.check x-show="copied" variant="solid"
                                                         class="text-green-500"></flux:icon>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    <div class="flex flex-col items-center space-y-3">
                        <x-input-otp
                            :digits="6"
                            name="code"
                            wire:model="code"
                            autocomplete="one-time-code"
                        />
                        @error('code')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center space-x-3">
                        <flux:button
                            variant="outline"
                            class="flex-1"
                            wire:click="resetVerification"
                        >
                            {{ __('Back') }}
                        </flux:button>
                        <flux:button
                            variant="primary"
                            class="flex-1"
                            wire:click="confirmTwoFactor"
                        >
                            {{ __('Confirm') }}
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:modal>
</section>
