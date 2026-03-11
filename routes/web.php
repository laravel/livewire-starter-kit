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

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('Materiales')) {
        return redirect()->route('admin.materials.index');
    } elseif ($user->hasRole('Produccion')) {
        return redirect()->route('admin.production.index');
    } elseif ($user->hasRole('Calidad')) {
        return redirect()->route('admin.quality.index');
    } elseif ($user->hasRole('Empaques')) {
        return redirect()->route('admin.packaging.index');
    } elseif ($user->hasRole('employee')) {
        return redirect()->route('employee.dashboard');
    }

    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
