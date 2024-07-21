<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

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
    Route::put('/{subdomain}/update', [RoleController::class, 'update'])->name('role.update');
    Route::post('/{subdomain}/update_links', [RoleController::class, 'updateLinks'])->name('role.update_links');
    Route::post('/{subdomain}/remove_links', [RoleController::class, 'removeLinks'])->name('role.remove_links');
    Route::get('/{subdomain}/add_member', [RoleController::class, 'createMember'])->name('role.create_member');
    Route::post('/{subdomain}/add_member', [RoleController::class, 'storeMember'])->name('role.store_member');
    Route::get('/{subdomain}/remove_member/{hash}', [RoleController::class, 'removeMember'])->name('role.remove_member');
    Route::get('/{subdomain1}/add_event/{subdomain2?}', [EventController::class, 'create'])->name('event.create');
    Route::get('/{subdomain1}/sign_up/{subdomain2?}', [EventController::class, 'create'])->name('event.sign_up');
    Route::post('/{subdomain1}/store_event/{subdomain2?}', [EventController::class, 'store'])->name('event.store');
    Route::get('/{subdomain}/event/{hash}', [EventController::class, 'edit'])->name('event.edit');
    Route::put('/{subdomain}/update_event/{hash}', [EventController::class, 'update'])->name('event.update');
    Route::get('/{subdomain}/accept_event/{hash}', [EventController::class, 'accept'])->name('event.accept');
    Route::get('/{subdomain}/decline_event/{hash}', [EventController::class, 'decline'])->name('event.decline');
    //Route::get('/{subdomain}/delete_event/{hash}', [EventController::class, 'delete'])->name('event.delete');
    Route::get('/{subdomain}/{tab?}/{year?}/{month?}/', [RoleController::class, 'viewAdmin'])->name('role.view_admin');
});

/*
Route::group(['domain' => 'dev.eventschedule.com'], function() {
    //
});

Route::group(['domain' => '{subdomain}.eventschedule.com'], function() {
    //
});
*/