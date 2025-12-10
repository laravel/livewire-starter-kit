<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        return view('livewire.admin.users.user-list');
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('livewire.admin.users.user-create');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('livewire.users.user-edit', compact('user'));
    }
}
