<?php

use Illuminate\Support\Facades\Route;
use Webkul\Saas\Http\Controllers\Tenant\BillingController;
use Webkul\Saas\Http\Controllers\Tenant\DashboardController;
use Webkul\Saas\Http\Controllers\Tenant\DomainController;
use Webkul\Saas\Http\Controllers\Tenant\OnboardingController;
use Webkul\Saas\Http\Controllers\Tenant\SettingsController;
use Webkul\Saas\Http\Controllers\Tenant\TeamController;
use Webkul\Saas\Http\Controllers\Tenant\UsageController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');

Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

Route::get('team', [TeamController::class, 'index'])->name('team.index');

Route::post('team', [TeamController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('team.store');

Route::put('team/{membership}', [TeamController::class, 'update'])->name('team.update');

Route::delete('team/{membership}', [TeamController::class, 'destroy'])->name('team.destroy');

Route::post('team/invitations/{invitation}/resend', [TeamController::class, 'resend'])
    ->middleware('throttle:5,1')
    ->name('team.resend');

Route::get('domains', [DomainController::class, 'index'])->name('domains.index');

Route::post('domains', [DomainController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('domains.store');

Route::post('domains/{domain}/verify', [DomainController::class, 'verify'])
    ->middleware('throttle:6,1')
    ->name('domains.verify');

Route::post('domains/{domain}/primary', [DomainController::class, 'primary'])->name('domains.primary');

Route::delete('domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');

Route::get('billing', [BillingController::class, 'index'])->name('billing.index');

Route::get('usage', [UsageController::class, 'index'])->name('usage.index');

Route::post('onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');
