<?php

use Illuminate\Support\Facades\Route;
use Webkul\Saas\Http\Controllers\SuperAdmin\AuditLogController;
use Webkul\Saas\Http\Controllers\SuperAdmin\AuthController;
use Webkul\Saas\Http\Controllers\SuperAdmin\BillingController;
use Webkul\Saas\Http\Controllers\SuperAdmin\DashboardController;
use Webkul\Saas\Http\Controllers\SuperAdmin\DomainController;
use Webkul\Saas\Http\Controllers\SuperAdmin\PlanController;
use Webkul\Saas\Http\Controllers\SuperAdmin\ProvisioningController;
use Webkul\Saas\Http\Controllers\SuperAdmin\SettingsController;
use Webkul\Saas\Http\Controllers\SuperAdmin\SubscriptionController;
use Webkul\Saas\Http\Controllers\SuperAdmin\TenantController;

Route::get('login', [AuthController::class, 'create'])->name('login');

Route::post('login', [AuthController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('login.store');

Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

Route::middleware('saas.super-admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('tenants', [TenantController::class, 'index'])
        ->middleware('saas.super-admin.ability:tenants.view')
        ->name('tenants.index');

    Route::get('tenants/create', [TenantController::class, 'create'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('tenants.create');

    Route::post('tenants', [TenantController::class, 'store'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('tenants.store');

    Route::get('tenants/{tenant}', [TenantController::class, 'show'])
        ->middleware('saas.super-admin.ability:tenants.view')
        ->name('tenants.show');

    Route::put('tenants/{tenant}', [TenantController::class, 'update'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('tenants.update');

    Route::post('tenants/{tenant}/approve', [TenantController::class, 'approve'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('tenants.approve');

    Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('tenants.activate');

    Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('tenants.suspend');

    Route::post('tenants/{tenant}/reactivate', [TenantController::class, 'reactivate'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('tenants.reactivate');

    Route::post('tenants/{tenant}/cancel', [TenantController::class, 'cancel'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('tenants.cancel');

    Route::delete('tenants/{tenant}', [TenantController::class, 'destroy'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('tenants.destroy');

    Route::post('tenants/{tenant}/export', [TenantController::class, 'export'])
        ->middleware('saas.super-admin.ability:tenants.view')
        ->name('tenants.export');

    Route::post('tenants/{tenant}/provision', [TenantController::class, 'provision'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('tenants.provision');

    Route::get('plans', [PlanController::class, 'index'])
        ->middleware('saas.super-admin.ability:plans.view')
        ->name('plans.index');

    Route::get('plans/create', [PlanController::class, 'create'])
        ->middleware('saas.super-admin.ability:plans.manage')
        ->name('plans.create');

    Route::post('plans', [PlanController::class, 'store'])
        ->middleware('saas.super-admin.ability:plans.manage')
        ->name('plans.store');

    Route::get('plans/{plan}', [PlanController::class, 'show'])
        ->middleware('saas.super-admin.ability:plans.view')
        ->name('plans.show');

    Route::get('plans/{plan}/edit', [PlanController::class, 'edit'])
        ->middleware('saas.super-admin.ability:plans.manage')
        ->name('plans.edit');

    Route::put('plans/{plan}', [PlanController::class, 'update'])
        ->middleware('saas.super-admin.ability:plans.manage')
        ->name('plans.update');

    Route::delete('plans/{plan}', [PlanController::class, 'destroy'])
        ->middleware('saas.super-admin.ability:plans.manage')
        ->name('plans.destroy');

    Route::get('subscriptions', [SubscriptionController::class, 'index'])
        ->middleware('saas.super-admin.ability:subscriptions.view')
        ->name('subscriptions.index');

    Route::get('subscriptions/{subscription}', [SubscriptionController::class, 'show'])
        ->middleware('saas.super-admin.ability:subscriptions.view')
        ->name('subscriptions.show');

    Route::post('subscriptions/{subscription}/extend-trial', [SubscriptionController::class, 'extendTrial'])
        ->middleware('saas.super-admin.ability:subscriptions.manage')
        ->name('subscriptions.extend_trial');

    Route::post('subscriptions/{subscription}/change-plan', [SubscriptionController::class, 'changePlan'])
        ->middleware('saas.super-admin.ability:subscriptions.manage')
        ->name('subscriptions.change_plan');

    Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])
        ->middleware('saas.super-admin.ability:subscriptions.manage')
        ->name('subscriptions.cancel');

    Route::post('subscriptions/{subscription}/reactivate', [SubscriptionController::class, 'reactivate'])
        ->middleware('saas.super-admin.ability:subscriptions.manage')
        ->name('subscriptions.reactivate');

    Route::get('billing', [BillingController::class, 'index'])
        ->middleware('saas.super-admin.ability:billing.view')
        ->name('billing.index');

    Route::get('billing/{invoice}', [BillingController::class, 'show'])
        ->middleware('saas.super-admin.ability:billing.view')
        ->name('billing.show');

    Route::post('billing/{invoice}/payments', [BillingController::class, 'recordPayment'])
        ->middleware('saas.super-admin.ability:billing.manage')
        ->name('billing.payments.store');

    Route::post('billing/mark-overdue', [BillingController::class, 'markOverdue'])
        ->middleware('saas.super-admin.ability:billing.manage')
        ->name('billing.mark_overdue');

    Route::get('domains', [DomainController::class, 'index'])
        ->middleware('saas.super-admin.ability:tenants.view')
        ->name('domains.index');

    Route::post('domains/{domain}/verify', [DomainController::class, 'verify'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('domains.verify');

    Route::post('domains/{domain}/primary', [DomainController::class, 'primary'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('domains.primary');

    Route::get('provisioning', [ProvisioningController::class, 'index'])
        ->middleware('saas.super-admin.ability:tenants.view')
        ->name('provisioning.index');

    Route::post('provisioning/{operation}/retry', [ProvisioningController::class, 'retry'])
        ->middleware('saas.super-admin.ability:tenants.manage')
        ->name('provisioning.retry');

    Route::get('settings', [SettingsController::class, 'index'])
        ->middleware('saas.super-admin.ability:settings.manage')
        ->name('settings.index');

    Route::put('settings', [SettingsController::class, 'update'])
        ->middleware('saas.super-admin.ability:settings.manage')
        ->name('settings.update');

    Route::get('audit', [AuditLogController::class, 'index'])
        ->middleware('saas.super-admin.ability:audit.view')
        ->name('audit.index');
});
