<?php

use Illuminate\Support\Facades\Route;
use Webkul\Saas\Http\Controllers\Api\OrderController;
use Webkul\Saas\Http\Controllers\Api\ProductController;
use Webkul\Saas\Http\Controllers\Api\TenantController;

Route::get('tenant', [TenantController::class, 'show'])->name('tenant.show');

Route::get('tenant/usage', [TenantController::class, 'usage'])->name('tenant.usage');

Route::get('products', [ProductController::class, 'index'])
    ->middleware('saas.feature:api_access')
    ->name('products.index');

Route::get('orders', [OrderController::class, 'index'])
    ->middleware('saas.feature:api_access')
    ->name('orders.index');
