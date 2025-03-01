<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome')->name('home');



Route::middleware(['auth','verified'])->group(function() {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Volt::route('contact', 'contact')->name('contact');
    Volt::route('skills', 'skills')->name('skills');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
