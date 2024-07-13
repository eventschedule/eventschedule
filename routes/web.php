<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RoleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/venue/new', [RoleController::class, 'newVenue']);
Route::get('/vendor/new', [RoleController::class, 'newVendor']);
Route::get('/talent/new', [RoleController::class, 'newTalent']);