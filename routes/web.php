<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::get('/home', function () {
    return view('home');
})->middleware(['auth', 'verified'])->name('home');

Route::get('/{subdomain}/view', [RoleController::class, 'viewGuest'])->name('role.view_guest');

Route::middleware('auth')->group(function () {
    Route::get('/sign_up', [RoleController::class, 'create'])->name('role.create');
    Route::post('/store', [RoleController::class, 'store'])->name('role.store');
    
    Route::get('/venues', [RoleController::class, 'viewVenues'])->name('venues');
    Route::get('/talent', [RoleController::class, 'viewTalent'])->name('talent');
    Route::get('/vendors', [RoleController::class, 'viewVendors'])->name('vendors');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/{subdomain}/edit', [RoleController::class, 'edit'])->name('role.edit');
    Route::post('/{subdomain}/links/update', [RoleController::class, 'updateLinks'])->name('role.update_links');
    Route::post('/{subdomain}/links/remove', [RoleController::class, 'removeLinks'])->name('role.remove_links');
    Route::put('/{subdomain}/update', [RoleController::class, 'update'])->name('role.update');
    Route::get('/{subdomain1}/event/create/{subdomain2?}', [EventController::class, 'create'])->name('event.create');


    Route::get('/{subdomain}/{tab?}', [RoleController::class, 'viewAdmin'])->name('role.view_admin');
    Route::get('/{subdomain}/{tab?}', [RoleController::class, 'viewAdmin'])->name('role.view_admin');
    Route::get('/{subdomain}/{tab?}', [RoleController::class, 'viewAdmin'])->name('role.view_admin');
});

require __DIR__.'/auth.php';
