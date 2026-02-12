<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileTrackerController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// File Tracker Routes
Route::get('/filetracker', [FileTrackerController::class, 'index'])->name('filetracker.index');

require __DIR__.'/settings.php';
