<?php

use App\Http\Controllers\Admin\ImageController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/images')
    ->name('admin.images.')
    ->group(function () {
        Route::get('/', [ImageController::class, 'index'])->name('index');
        Route::post('/', [ImageController::class, 'store'])->name('store');
        Route::put('/{image}', [ImageController::class, 'update'])->whereUuid('image')->name('update');
        Route::delete('/{image}', [ImageController::class, 'destroy'])->whereUuid('image')->name('destroy');
    });
