<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Materials Area Routes
|--------------------------------------------------------------------------
|
| Routes for the Materials Area module. Requires materials permissions.
|
*/

// Materials Area Routes (requires Materials role)
Route::middleware(['auth', 'verified', 'permission:view_materials_area'])->group(function () {
    Route::get('/', \App\Livewire\Admin\Materials\MaterialsAreaDashboard::class)->name('dashboard');
});

// Quality Area Routes (requires Quality role)
// Route::middleware(['auth', 'verified', 'permission:view_quality_area'])->group(function () {
//     Route::get('/', \App\Livewire\Admin\Quality\QualityApprovalInterface::class)->name('dashboard');
// });
