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

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('employee') && !$user->hasRole('admin')) {
        return redirect()->route('employee.dashboard');
    } elseif ($user->hasRole('Materiales') && !$user->hasRole('admin')) {
        return redirect()->route('admin.materials.index');
    } elseif ($user->hasRole('Produccion') && !$user->hasRole('admin')) {
        return redirect()->route('admin.production.index');
    } elseif ($user->hasRole('Calidad') && !$user->hasRole('admin')) {
        return redirect()->route('admin.quality.index');
    }

    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
