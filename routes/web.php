<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\UserController;
use App\Http\Controllers\POController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ShiftController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

/* Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');*/

require __DIR__.'/auth.php';
