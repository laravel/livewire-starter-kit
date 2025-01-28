<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Route::get('/dashboard', function () {
//     //Auth::logout();
    
// })->middleware(['auth', 'verified'])->name('dashboard');


Volt::route('settings/profile', 'settings.profile')
    ->name('settings.profile')->middleware('password.confirm');

Volt::route('settings/password', 'settings.password')
    ->name('settings.password');

Volt::route('settings/appearance', 'settings.appearance')
    ->name('settings.appearance');

require __DIR__.'/auth.php';