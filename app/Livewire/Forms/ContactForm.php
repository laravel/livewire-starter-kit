<?php

namespace App\Livewire\Forms;

use App\Models\Contact;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ContactForm extends Form
{

    #[Validate('required|string|max:1000')]
    public $summary = '';

    #[Validate('required|string|max:100')]
    public $name = '';

    #[Validate('required|url|max:255')]
    public $profile_pic = '';

    #[Validate('required|email|max:255')]
    public $email = '';

    #[Validate('required|numeric|digits_between:10,15')]
    public $phone = '';

    #[Validate('required|string|max:255')]
    public $address = '';

    #[Validate('required|string|max:100')]
    public $linkedin = '';

    #[Validate('required|string|max:100')]
    public $github = '';

    #[Validate('required|string|max:100')]
    public $x = '';

    #[Validate('required|url|max:255')]
    public $website = '';

    public function setContact()
    {
        $this->fill(Contact::pluck('value', 'value_type'));
    }

    public function save()
    {
        $validated = $this->validate();

        Contact::upsert(collect($validated)->map(function ($value, $key) {
            return [
                'value_type' => $key,
                'value' => $value,
            ];
        })->toArray(), ['value_type']);
    }
}
