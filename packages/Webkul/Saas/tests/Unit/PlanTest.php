<?php

use Webkul\Saas\Enums\FeatureKey;
use Webkul\Saas\Models\Plan;

// ==== Plan Limits ====

it('returns the configured numeric limit', function () {
    $plan = new Plan(['limits' => ['max_products' => 25]]);

    expect($plan->limit('max_products'))->toBe(25)
        ->and($plan->limit(FeatureKey::MaxProducts))->toBe(25);
});

it('treats a missing key as unlimited', function () {
    $plan = new Plan(['limits' => []]);

    expect($plan->limit('max_products'))->toBeNull();
});

it('treats an explicit null as unlimited', function () {
    $plan = new Plan(['limits' => ['max_products' => null]]);

    expect($plan->limit('max_products'))->toBeNull();
});

it('casts a numeric string limit to an integer', function () {
    $plan = new Plan(['limits' => ['max_staff' => '4']]);

    expect($plan->limit('max_staff'))->toBe(4);
});

// ==== Plan Features ====

it('reports whether a feature is included', function () {
    $plan = new Plan(['features' => ['custom_domain' => true, 'api_access' => false]]);

    expect($plan->includes('custom_domain'))->toBeTrue()
        ->and($plan->includes('api_access'))->toBeFalse()
        ->and($plan->includes('premium_support'))->toBeFalse();
});

// ==== Monthly Revenue ====

it('normalises a monthly price unchanged', function () {
    $plan = new Plan(['price' => '29.00', 'billing_interval' => 'monthly']);

    expect($plan->monthlyPrice())->toBe(29.0);
});

it('normalises a quarterly price to a third', function () {
    $plan = new Plan(['price' => '90.00', 'billing_interval' => 'quarterly']);

    expect($plan->monthlyPrice())->toBe(30.0);
});

it('normalises a yearly price to a twelfth', function () {
    $plan = new Plan(['price' => '120.00', 'billing_interval' => 'yearly']);

    expect($plan->monthlyPrice())->toBe(10.0);
});
