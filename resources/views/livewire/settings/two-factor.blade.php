<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

new class extends Component {
    public $enabled = false;
    public $confirmed = false;

    #[Validate('required|min:6')]
    public $authCode;

    public $secret = '';
    public $codes = '';
    public $qr = '';
    public $showRecoveryCodes = true;

    public function mount()
    {
        if (is_null(auth()->user()->two_factor_confirmed_at)) {
            app(DisableTwoFactorAuthentication::class)(auth()->user());
        } else {
            $this->confirmed = true;
            $this->showRecoveryCodes = false;
        }
    }

    public function enable()
    {
        app(EnableTwoFactorAuthentication::class)(auth()->user());
        $this->qr = auth()->user()->twoFactorQrCodeSvg();
        $this->secret = decrypt(auth()->user()->two_factor_secret);
        $this->codes = json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true);
        $this->enabled = true;
    }


    public function regenerateCodes(GenerateNewRecoveryCodes $generate)
    {
        $generate(Auth::user());
    }

    #[On('submitCode')]
    public function submitCode($code = null)
    {
        if ($code) {
            $this->authCode = $code;
        }

        $this->validate();
        app(ConfirmTwoFactorAuthentication::class)(auth()->user(), $this->authCode);
        $this->qr = null;
        $this->secret = null;
    }

    public function disable()
    {
        app(DisableTwoFactorAuthentication::class)(auth()->user());

        $this->enabled = false;
        $this->confirmed = false;
    }


}

?>

<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.layout :heading="__('Two Factor Authentication')" :subheading="__('Manage your two-factor authentication settings')">
        <div x-data="{ showRecoveryCodes: '{{ $showRecoveryCodes }}', verify: false }"
            class="flex flex-col w-full mx-auto text-sm">
            @if(!$confirmed)
                <div class="relative flex flex-col items-start rounded-xl justify-start space-y-5">
                    <flux:badge color="red">Disabled</flux:badge>
                    <p class="-translate-y-1 text-stone-500 dark:text-stone-400">When you enable 2FA, you’ll be prompted for
                        a secure code during login, which can be retrieved from your phone's Google Authenticator app.</p>
                    <flux:modal.trigger name="edit-profile">
                        <div class="w-auto">
                            <flux:button variant="primary" icon="shield-check" class="w-full" wire:click="enable()">
                                {{ __('Enable') }}
                            </flux:button>
                        </div>
                    </flux:modal.trigger>
                </div>

                <flux:modal name="edit-profile" class="md:w-full md:max-w-md">
                    <div class="relative w-full items-center justify-center flex flex-col space-y-5">
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
                                <flux:icon.scan-line class="size-6 relative z-20 dark:text-black" />
                            </div>
                        </div>
                        <div class="space-y-2 flex flex-col items-center justify-center">
                            <h2 class="text-xl font-medium text-stone-900 dark:text-stone-100">
                                <span x-show="!verify">{{ __('Turn on 2-step Verification') }}</span>
                                <span x-show="verify" x-cloak>{{ __('Verify Authentication Code') }}</span>
                            </h2>
                            <p class="text-stone-600 dark:text-stone-400">
                                <span
                                    x-show="!verify">{{ __('Open your authenticator app and choose Scan QR code') }}</span>
                                <span x-show="verify"
                                    x-cloak>{{ __('Enter the 6-digit code from your authenticator app') }}</span>
                            </p>
                        </div>
                        <div x-show="!verify" class="w-full">
                            <div class="relative max-w-md mx-auto overflow-hidden flex items-center p-8 pt-0">
                                <div
                                    class="border border-stone-200 dark:border-stone-700 rounded-lg relative overflow-hidden w-64 aspect-square mx-auto">
                                    <div wire:loading.flex wire:target="enable"
                                        class="bg-white dark:bg-stone-700 animate-pulse flex items-center justify-center absolute inset-0 w-full h-auto aspect-square z-10">
                                        <flux:icon.loader-circle class="size-6 animate-spin" />
                                    </div>
                                    <div wire:loading.remove wire:target="enable"
                                        class="relative z-10 flex items-center justify-center p-4">
                                        <div class="flex aspect-square size-full items-center justify-center">
                                            {!! $qr !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-5 w-full">
                                <flux:button variant="primary" class="w-full"
                                    x-on:click="verify = true; $wire.call('$refresh')">{{ __('Continue') }}</flux:button>
                            </div>
                            <div class="flex items-center relative w-full justify-center py-2">
                                <div class="w-full absolute inset-0 top-1/2 bg-stone-200 dark:bg-stone-600 h-px"></div>
                                <span
                                    class="px-2 py-1 bg-white dark:bg-stone-800 relative">{{ __('or, enter the code manually') }}</span>
                            </div>
                            <div class="flex items-center justify-center w-full space-x-2" x-data="{
                                                copied: false,
                                                copyToClipboard() {
                                                    if (navigator && navigator.clipboard) {
                                                        navigator.clipboard.writeText('{{ $secret }}');
                                                    } else {
                                                        console.warn('Clipboard API is only available in secure contexts (HTTPS) or localhost.');
                                                    }
                                                    this.copied = true;
                                                    setTimeout(() => {
                                                        this.copied = false;
                                                    }, 1500);
                                                }
                                            }">
                                <div
                                    class="w-full rounded-xl flex items-stretch border dark:border-stone-700 overflow-hidden">
                                    <div wire:loading.flex wire:target="enable"
                                        class="w-full h-full flex items-center justify-center bg-stone-100 dark:bg-stone-700 p-3">
                                        <flux:icon.loader-circle class="size-4 animate-spin" />
                                    </div>
                                    @if($enabled)
                                        <input wire:loading.remove wire:target="enable" type="text" readonly
                                            value="{{ $secret }}" class="w-full h-full p-3 text-black dark:text-stone-100" />
                                        <button wire:loading.remove wire:target="enable" x-on:click="copyToClipboard()"
                                            class="block relative border-l border-stone-200 dark:border-stone-600 px-3 hover:bg-stone-100 dark:hover:bg-stone-600 h-auto">
                                            <flux:icon x-show="!copied" icon="copy" class="w-4"></flux:icon>
                                            <flux:icon x-show="copied" icon="check" class="w-4 text-green-500"></flux:icon>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div x-show="verify" x-cloak
                            class="relative flex w-auto flex-col items-center justify-center space-y-5">
                            <x-input-otp :digits="6" wire:model="authCode" id="two_factor_auth_code"
                                eventCallback="submitCode" />
                            @error('code')
                                <p class="my-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <div class="flex items-center space-x-5 w-full">
                                <flux:button variant="outline" class="w-full" x-on:click="verify = false">{{ __('Back') }}
                                </flux:button>
                                <flux:button variant="primary" class="w-full" wire:click="submitCode({{ $this->authCode }})"
                                    wire:target="submitCode">{{ __('Confirm') }}</flux:button>
                            </div>
                        </div>
                    </div>
                </flux:modal>
            @else
                <div class="flex flex-col space-y-5">
                    <div class="relative">
                        <flux:badge color="green">Enabled</flux:badge>
                    </div>
                    <p>With two factor authentication enabled, you’ll be prompted for a secure, random token during login,
                        which you can retrieve from your Google Authenticator app.</p>

                    <div>
                        <flux:callout icon="lock-keyhole-open" color="gray" inline class="rounded-b-none">
                            <flux:callout.heading>2FA Recovery Codes</flux:callout.heading>
                            <flux:callout.text>
                                Recovery codes let you regain access if you lose your 2FA device. Store them in a secure
                                password manager.
                            </flux:callout.text>
                        </flux:callout>
                        <div
                            class="bg-stone-100 dark:bg-stone-800 rounded-b-xl border-t-0 border border-stone-200 dark:border-stone-700 text-sm">
                            <div x-on:click="showRecoveryCodes = !showRecoveryCodes"
                                class="h-10 group cursor-pointer flex items-center select-none justify-between px-5 text-xs"
                                x-cloak>
                                <div :class="{ 'opacity-40 group-hover:opacity-60': !showRecoveryCodes, 'opacity-60': showRecoveryCodes }"
                                    class="relative">
                                    <span x-show="!showRecoveryCodes" class="flex items-center space-x-1">
                                        <flux:icon.eye class="size-4" /> <span>View My Recovery Codes</span>
                                    </span>
                                    <span x-show="showRecoveryCodes" class="flex items-center space-x-1"
                                        x-cloak><flux:icon.eye-off class="size-4" /> <span>Hide Recovery Codes</span></span>
                                </div>
                                <flux:button x-show="showRecoveryCodes" size="xs" variant="filled" class="text-stone-600"
                                    wire:click="regenerateCodes"
                                    x-on:click="$event.preventDefault(); $event.stopPropagation();">
                                    {{ __('Regenerate Codes') }}
                                </flux:button>
                            </div>
                            <div x-show="showRecoveryCodes" class="relative" x-collapse x-cloak>
                                <div
                                    class="grid max-w-xl gap-1 px-4 py-4 font-mono text-sm bg-stone-200 dark:bg-stone-900 dark:text-stone-100">
                                    @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                                        <div>{{ $code }}</div>
                                    @endforeach
                                </div>
                                <p class="px-4 py-3 text-xs select-none text-stone-500 dark:text-stone-400">
                                    You have {{ count(json_decode(decrypt(auth()->user()->two_factor_recovery_codes))) }}
                                    recovery codes left. Each can be used once to access your account and will be removed
                                    after use. If you need more, click <span class="font-bold">Regenerate Codes</span>
                                    above.</p>
                            </div>
                        </div>
                    </div>
                    <div class="inline relative">
                        <flux:button variant="danger" type="submit" class="w-auto" wire:click="disable">
                            {{ __('Disable 2FA') }}
                        </flux:button>
                    </div>
                </div>

            @endif
        </div>

    </x-settings.layout>
</section>
