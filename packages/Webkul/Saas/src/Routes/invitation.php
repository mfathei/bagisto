<?php

use Illuminate\Support\Facades\Route;
use Webkul\Saas\Http\Controllers\InvitationController;

Route::get('{token}', [InvitationController::class, 'show'])->name('show');

Route::post('{token}', [InvitationController::class, 'accept'])
    ->middleware('throttle:10,1')
    ->name('accept');
