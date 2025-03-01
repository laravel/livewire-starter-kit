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

<flux:container>
    <flux:col>
        <flux:heading size="lg">Basic Information</flux:heading>
        <flux:subheading>Fill in the details and save</flux:subheading>
    </flux:col>
    <flux:separator />
    <form wire:submit="save">
        <flux:col>
            <flux:fieldset>
                <flux:legend>About</flux:legend>
                <flux:textarea wire:model="form.summary" label="Summary"
                    placeholder="Write a brief summary of yourself." />
            </flux:fieldset>
        </flux:col>
        <flux:separator />
        <flux:col>
            <flux:fieldset>
                <flux:legend>Basic Information</flux:legend>
                <flux:grid cols="2">
                    <flux:input wire:model="form.name" label="Name" placeholder="John Doe" />
                    <flux:input wire:model="form.email" label="Email" placeholder="summonshr@gmail.com" />
                    <flux:input wire:model="form.phone" label="Phone" placeholder="+1 (555) 123-4567" />
                    <flux:input wire:model="form.address" label="Address" placeholder="123 Main St, City, State, 12345" />
                </flux:grid>
            </flux:fieldset>
        </flux:col>
        <flux:separator />
        <flux:col>
            <flux:fieldset>
                <flux:legend>Social profile</flux:legend>
                <flux:grid cols="2">
                    <flux:input icon="linkedin" wire:model="form.linkedin" label="LinkedIn"
                        placeholder="https://linkedin.com/in/suman-shresth" />
                    <flux:input icon="x" wire:model="form.x" label="X"
                        placeholder="https://twitter/sumfreelancer" />
                    <flux:input icon="github" wire:model="form.github" label="GitHub"
                        placeholder="https://github.com/summonshr" />
                    <flux:input icon="website" wire:model="form.website" label="Website"
                        placeholder="https://example.com" />
                </flux:grid>
            </flux:fieldset>
        </flux:col>
        <flux:separator />
        <flux:row>
            <flux:spacer />
            <flux:button wire:loading.attr="disabled" type="submit">
                Save
            </flux:button>
        </flux:row>
    </form>
</flux:container>
