<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;

class UserShow extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->load(['roles', 'areas.department']);
    }

    public function render()
    {
        return view('livewire.admin.users.user-show');
    }
}
