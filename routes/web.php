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
    
    Route::get('/venues', [RoleController::class, 'viewVenues'])->name('role.view_venues');
    Route::get('/talent', [RoleController::class, 'viewTalents'])->name('role.view_talents');
    Route::get('/vendors', [RoleController::class, 'viewVendors'])->name('role.view_vendors');

    Route::get('/venue/{venue}', [RoleController::class, 'viewVenue'])->name('role.view_venue');
    Route::get('/talent/{talent}', [RoleController::class, 'viewTalent'])->name('role.view_talent');
    Route::get('/vendor/{vendor}', [RoleController::class, 'viewVendor'])->name('role.view_vendor');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
