<?php

use Livewire\Volt\Component;
use App\Models\Contact;
use App\Livewire\Forms\ContactForm;

new class extends Component {
    public ContactForm $form;

    public function mount()
    {
        $this->form->setContact();
    }

    public function save()
    {
        $this->form->save();
    }
}; ?>

<div class="w-full">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl overflow-hidden">
        <div class=" px-6 py-4">
            <flux:heading>Basic Information</flux:heading>
            <flux:subheading>Fill in the details and save</flux:subheading>
        </div>

        <form wire:submit="save" class="p-6">
            <flux:fieldset>
                <flux:legend>Basic Information</flux:legend>
                <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                    <flux:input wire:model="form.name" label="Name" placeholder="John Doe" />
                    <flux:input wire:model="form.email" label="Email" placeholder="summonshr@gmail.com" />
                    <flux:input wire:model="form.phone" label="Phone" placeholder="+1 (555) 123-4567" />
                    <flux:input wire:model="form.address" label="Address" placeholder="123 Main St, City, State, 12345" />
                </div>
            </flux:fieldset>
            <flux:separator class="my-6" />

            <flux:fieldset>
                <flux:legend>Social profile</flux:legend>
                <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                    <flux:input icon="linkedin" wire:model="form.linkedin" label="LinkedIn" placeholder="https://linkedin.com/in/suman-shresth" />
                    <flux:input icon="x" wire:model="form.x" label="X" placeholder="https://twitter/sumfreelancer" />
                    <flux:input icon="github" wire:model="form.github" label="GitHub" placeholder="https://github.com/summonshr" />
                    <flux:input icon="website" wire:model="form.website" label="Website" placeholder="https://example.com" />
                </div>
            </flux:fieldset>
            <div class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-end space-x-3">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-800 pointer-cursor hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled" wire:target="save">
                        <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span>Save Contact</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
