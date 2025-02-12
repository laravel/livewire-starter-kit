<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout heading="Appearance" subheading="Update your account's appearance settings">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">Light</flux:radio>
            <flux:radio value="dark" icon="moon">Dark</flux:radio>
            <flux:radio value="system" icon="computer-desktop">System</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</div>
