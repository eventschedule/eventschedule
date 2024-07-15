<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::get('/home', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/sign_up', [RoleController::class, 'create'])->name('role.create');
    Route::post('/store', [RoleController::class, 'store'])->name('role.store');
    Route::get('/edit/{subdomain}', [RoleController::class, 'edit'])->name('role.edit');
    Route::post('/update/{subdomain}', [RoleController::class, 'update'])->name('role.update');
    
    Route::get('/venues', [RoleController::class, 'viewVenues'])->name('venues');
    Route::get('/talent', [RoleController::class, 'viewTalent'])->name('talent');
    Route::get('/vendors', [RoleController::class, 'viewVendors'])->name('vendors');

    Route::get('/venue/{subdomain}', [RoleController::class, 'view'])->name('role.view');
    Route::get('/talent/{subdomain}', [RoleController::class, 'view'])->name('role.view');
    Route::get('/vendor/{subdomain}', [RoleController::class, 'view'])->name('role.view');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
