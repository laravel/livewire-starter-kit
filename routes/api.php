<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to "api" middleware group. Make something great!
|
*/

// Category API Routes
Route::apiResource('categories', CategoryController::class);
Route::post('categories/{id}/restore', [CategoryController::class, 'restore']);
Route::delete('categories/{id}/force-delete', [CategoryController::class, 'forceDelete']);
Route::get('categories/trashed', [CategoryController::class, 'trashed']);

// Document API Routes
Route::apiResource('documents', DocumentController::class);
