<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::get('/home', function () {
    return view('home');
})->middleware(['auth', 'verified'])->name('home');

Route::get('/view/{subdomain}', [RoleController::class, 'viewGuest'])->name('role.view_guest');

Route::middleware('auth')->group(function () {
    Route::get('/sign_up', [RoleController::class, 'create'])->name('role.create');
    Route::post('/store', [RoleController::class, 'store'])->name('role.store');
    Route::get('/edit/{subdomain}', [RoleController::class, 'edit'])->name('role.edit');
    Route::post('/update/links/{subdomain}', [RoleController::class, 'updateLinks'])->name('role.update_links');
    Route::post('/remove/links/{subdomain}', [RoleController::class, 'removeLinks'])->name('role.remove_links');
    Route::put('/update/{subdomain}', [RoleController::class, 'update'])->name('role.update');
    
    Route::get('/venues', [RoleController::class, 'viewVenues'])->name('venues');
    Route::get('/talent', [RoleController::class, 'viewTalent'])->name('talent');
    Route::get('/vendors', [RoleController::class, 'viewVendors'])->name('vendors');

    Route::get('/venue/{subdomain}/{tab?}', [RoleController::class, 'viewAdmin'])->name('role.view_admin');
    Route::get('/talent/{subdomain}/{tab?}', [RoleController::class, 'viewAdmin'])->name('role.view_admin');
    Route::get('/vendor/{subdomain}/{tab?}', [RoleController::class, 'viewAdmin'])->name('role.view_admin');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
