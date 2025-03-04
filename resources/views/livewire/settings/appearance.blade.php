<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun" class="cursor-pointer">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon" class="cursor-pointer">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop" class="cursor-pointer">{{ __('System') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</div>
