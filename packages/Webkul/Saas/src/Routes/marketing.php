<?php

use Illuminate\Support\Facades\Route;
use Webkul\Saas\Http\Controllers\Marketing\ContactController;
use Webkul\Saas\Http\Controllers\Marketing\LegalController;
use Webkul\Saas\Http\Controllers\Marketing\LocaleController;
use Webkul\Saas\Http\Controllers\Marketing\MarketingPageController;
use Webkul\Saas\Http\Controllers\Marketing\MerchantLoginController;
use Webkul\Saas\Http\Controllers\Marketing\PricingController;
use Webkul\Saas\Http\Controllers\Marketing\SitemapController;

Route::get('/', [MarketingPageController::class, 'home'])->name('home');

Route::get('features', [MarketingPageController::class, 'features'])->name('features');

Route::get('solutions', [MarketingPageController::class, 'solutions'])->name('solutions');

Route::get('pricing', [PricingController::class, 'index'])->name('pricing');

Route::get('resources', [MarketingPageController::class, 'resources'])->name('resources');

Route::get('about', [MarketingPageController::class, 'about'])->name('about');

Route::get('contact', [ContactController::class, 'show'])->name('contact');

Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:saas-contact')
    ->name('contact.store');

Route::get('merchant-login', [MerchantLoginController::class, 'show'])->name('merchant-login');

Route::post('merchant-login', [MerchantLoginController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('merchant-login.store');

Route::get('locale/{locale}', [LocaleController::class, 'update'])->name('locale');

Route::get('privacy', [LegalController::class, 'show'])
    ->defaults('page', 'privacy')
    ->name('privacy');

Route::get('terms', [LegalController::class, 'show'])
    ->defaults('page', 'terms')
    ->name('terms');

Route::get('refund-policy', [LegalController::class, 'show'])
    ->defaults('page', 'refund-policy')
    ->name('refund-policy');

Route::get('sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');

Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');
