<?php

use Illuminate\Support\Facades\Route;
use Webkul\Saas\Http\Controllers\Platform\RegistrationController;

Route::get('register', [RegistrationController::class, 'create'])->name('create');

Route::post('register', [RegistrationController::class, 'store'])
    ->middleware('throttle:saas-registration')
    ->name('store');

Route::get('register/pending', [RegistrationController::class, 'pending'])->name('pending');

Route::post('register/resend', [RegistrationController::class, 'resend'])
    ->middleware('throttle:3,10')
    ->name('resend');

Route::get('verify/{tenant}', [RegistrationController::class, 'verify'])
    ->middleware('signed')
    ->name('verify');
