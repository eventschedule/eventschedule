<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('sign_up', [RegisteredUserController::class, 'create'])
        ->name('sign_up');

    Route::post('sign_up/send-code', [RegisteredUserController::class, 'sendVerificationCode'])
        ->name('sign_up.send_code')
        ->middleware('throttle:5,1');

    Route::post('sign_up', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:5,1');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1');

    Route::get('reset-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('reset-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email')
        ->middleware('throttle:5,1');

    Route::get('update-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('update-password', [NewPasswordController::class, 'store'])
        ->name('password.store')
        ->middleware('throttle:5,1');

    Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle'])
        ->name('auth.google');

    Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])
        ->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware('throttle:5,1');

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Google re-authentication for setting password (for Google-only users)
    Route::get('auth/google/set-password', [SocialAuthController::class, 'redirectToGoogleForSetPassword'])
        ->name('auth.google.set_password');

    Route::get('auth/google/set-password/callback', [SocialAuthController::class, 'handleGoogleCallbackForSetPassword'])
        ->name('auth.google.set_password.callback');

    // Google account connection from settings (for existing users who want to link Google)
    Route::get('auth/google/connect', [SocialAuthController::class, 'redirectToGoogleConnect'])
        ->name('auth.google.connect');

    Route::get('auth/google/connect/callback', [SocialAuthController::class, 'handleGoogleConnectCallback'])
        ->name('auth.google.connect.callback');

    Route::post('auth/google/disconnect', [SocialAuthController::class, 'disconnectGoogle'])
        ->name('auth.google.disconnect');
});
