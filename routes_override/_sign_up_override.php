<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/sign_up', function () {
        try {
            if (function_exists('view') && view()->exists('auth.register')) {
                return view('auth.register');
            }
        } catch (\Throwable $e) {
            // fall through to controller
        }

        // Fallback: call the app's controller if the view isn't present
        if (class_exists(\App\Http\Controllers\Auth\RegisteredUserController::class)) {
            return app(\App\Http\Controllers\Auth\RegisteredUserController::class)
                ->create(request());
        }

        abort(404);
    })->name('sign_up');
});
