<?php

use Illuminate\Validation\ValidationException;
use Webkul\Saas\Enums\BillingStatus;
use Webkul\Saas\Enums\SubscriptionStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Services\BillingService;
use Webkul\Saas\Services\DomainService;
use Webkul\Saas\Services\SubscriptionService;
use Webkul\Saas\Services\TenantManager;
use Webkul\Saas\Services\TenantResolver;

// ==== Trial Lifecycle ====

it('starts a paid plan on trial', function () {
    $plan = $this->createPlan(['price' => 20, 'trial_days' => 14]);
    $tenant = $this->createTenant(['plan_id' => $plan->getKey()]);

    $subscription = app(SubscriptionService::class)->start($tenant, $plan);

    expect($subscription->status)->toBe(SubscriptionStatus::Trialing)
        ->and($subscription->trial_ends_at)->not->toBeNull()
        ->and($subscription->trial_ends_at->isFuture())->toBeTrue()
        ->and($tenant->refresh()->current_subscription_id)->toBe($subscription->getKey());
});

it('starts a free plan as active without a trial', function () {
    $plan = $this->createPlan(['price' => 0, 'trial_days' => 14]);
    $tenant = $this->createTenant(['plan_id' => $plan->getKey()]);

    $subscription = app(SubscriptionService::class)->start($tenant, $plan);

    expect($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->trial_ends_at)->toBeNull();
});

it('moves an expired trial past due and then suspends after the grace period', function () {
    $plan = $this->createPlan(['price' => 20, 'trial_days' => 7]);
    $tenant = $this->createTenant(['plan_id' => $plan->getKey()]);

    $subscription = app(SubscriptionService::class)->start($tenant, $plan);

    $this->travel(8)->days();

    expect(app(SubscriptionService::class)->processLifecycle())->toBe(1);

    $subscription->refresh();

    expect($subscription->status)->toBe(SubscriptionStatus::PastDue)
        ->and($subscription->grace_ends_at)->not->toBeNull();

    $this->travel(4)->days();

    expect(app(SubscriptionService::class)->processLifecycle())->toBe(1)
        ->and($subscription->refresh()->status)->toBe(SubscriptionStatus::Suspended);

    app(SubscriptionService::class)->resume($tenant);

    expect($subscription->refresh()->status)->toBe(SubscriptionStatus::Active);
});

it('cancels a subscription at the end of its period', function () {
    $plan = $this->createPlan(['price' => 15, 'trial_days' => 0]);
    $tenant = $this->createTenant(['plan_id' => $plan->getKey()]);
    $service = app(SubscriptionService::class);

    $subscription = $service->start($tenant, $plan);

    $service->cancel($tenant, true, 'Customer request');

    expect($subscription->refresh()->cancel_at_period_end)->toBeTrue();

    $subscription->forceFill(['current_period_end' => now()->subMinute()])->save();

    expect($service->processLifecycle())->toBe(1)
        ->and($subscription->refresh()->status)->toBe(SubscriptionStatus::Cancelled);
});

it('rejects an invalid trial extension', function () {
    $plan = $this->createPlan(['price' => 20, 'trial_days' => 7]);
    $tenant = $this->createTenant(['plan_id' => $plan->getKey()]);

    app(SubscriptionService::class)->start($tenant, $plan);

    expect(fn () => app(SubscriptionService::class)->extendTrial($tenant, 0, 'Invalid'))
        ->toThrow(ValidationException::class);
});

// ==== Invoicing ====

it('invoices an elapsed period once', function () {
    $plan = $this->createPlan(['price' => 50, 'trial_days' => 0]);
    $tenant = $this->createTenant(['plan_id' => $plan->getKey()]);
    $subscriptions = app(SubscriptionService::class);
    $billing = app(BillingService::class);

    $subscription = $subscriptions->start($tenant, $plan);
    $subscription->forceFill(['current_period_end' => now()->subDay()])->save();

    expect($billing->invoiceDuePeriods())->toBe(1)
        ->and($billing->invoiceDuePeriods())->toBe(0);

    $invoice = $billing->forTenant($tenant)->first();

    expect($invoice->status)->toBe(BillingStatus::Open)
        ->and((float) $invoice->total)->toBe(50.0)
        ->and($invoice->balance())->toBe(50.0);
});

it('flags an overdue invoice and its subscription past due', function () {
    $plan = $this->createPlan(['price' => 30, 'trial_days' => 0]);
    $tenant = $this->createTenant(['plan_id' => $plan->getKey()]);
    $billing = app(BillingService::class);

    $subscription = app(SubscriptionService::class)->start($tenant, $plan);
    $subscription->forceFill(['current_period_end' => now()->subDays(10)])->save();

    $billing->invoiceDuePeriods();

    $this->travel(8)->days();

    expect($billing->markOverdueInvoices())->toBe(1);

    $invoice = $billing->forTenant($tenant)->first();

    expect($invoice->status)->toBe(BillingStatus::PastDue)
        ->and($subscription->refresh()->status)->toBe(SubscriptionStatus::PastDue);
});

// ==== Payments ====

it('records partial payments and restores service once paid in full', function () {
    $plan = $this->createPlan(['price' => 100, 'trial_days' => 0]);
    $tenant = $this->createTenant(['plan_id' => $plan->getKey()]);
    $billing = app(BillingService::class);

    $subscription = app(SubscriptionService::class)->start($tenant, $plan);
    $subscription->forceFill(['current_period_end' => now()->subDay()])->save();

    $billing->invoiceDuePeriods();
    $this->travel(8)->days();
    $billing->markOverdueInvoices();

    $invoice = $billing->forTenant($tenant)->first();

    $billing->recordPayment($invoice, [
        'amount' => 40,
        'currency' => 'USD',
        'reference' => 'BANK-TRANSFER-1',
        'method' => 'bank_transfer',
        'confirmed' => true,
    ]);

    $invoice->refresh();

    expect((float) $invoice->amount_paid)->toBe(40.0)
        ->and($invoice->status)->toBe(BillingStatus::Open)
        ->and($invoice->transactions()->count())->toBe(1);

    $billing->recordPayment($invoice, [
        'amount' => 40,
        'currency' => 'USD',
        'reference' => 'BANK-TRANSFER-1',
        'method' => 'bank_transfer',
        'confirmed' => true,
    ]);

    expect((float) $invoice->refresh()->amount_paid)->toBe(40.0)
        ->and($invoice->transactions()->count())->toBe(1);

    $billing->recordPayment($invoice, [
        'amount' => 60,
        'currency' => 'USD',
        'reference' => 'BANK-TRANSFER-2',
        'method' => 'bank_transfer',
        'confirmed' => true,
    ]);

    $invoice->refresh();

    expect($invoice->status)->toBe(BillingStatus::Paid)
        ->and($invoice->paid_at)->not->toBeNull()
        ->and($subscription->refresh()->status)->toBe(SubscriptionStatus::Active);
});

it('rejects a payment in the wrong currency', function () {
    $plan = $this->createPlan(['price' => 25, 'trial_days' => 0]);
    $tenant = $this->createTenant(['plan_id' => $plan->getKey()]);
    $billing = app(BillingService::class);

    $subscription = app(SubscriptionService::class)->start($tenant, $plan);
    $subscription->forceFill(['current_period_end' => now()->subDay()])->save();

    $billing->invoiceDuePeriods();

    $invoice = $billing->forTenant($tenant)->first();

    expect(fn () => $billing->recordPayment($invoice, [
        'amount' => 25,
        'currency' => 'EUR',
        'reference' => 'WRONG-CURRENCY',
        'confirmed' => true,
    ]))->toThrow(ValidationException::class);

    expect($invoice->transactions()->count())->toBe(0);
});

it('rejects a manual payment that was not explicitly confirmed', function () {
    $plan = $this->createPlan(['price' => 25, 'trial_days' => 0]);
    $tenant = $this->createTenant(['plan_id' => $plan->getKey()]);
    $billing = app(BillingService::class);

    $subscription = app(SubscriptionService::class)->start($tenant, $plan);
    $subscription->forceFill(['current_period_end' => now()->subDay()])->save();

    $billing->invoiceDuePeriods();

    $invoice = $billing->forTenant($tenant)->first();

    expect(fn () => $billing->recordPayment($invoice, [
        'amount' => 25,
        'currency' => 'USD',
        'reference' => 'UNCONFIRMED',
        'confirmed' => false,
    ]))->toThrow(ValidationException::class);

    expect((float) $invoice->refresh()->amount_paid)->toBe(0.0);
});

// ==== Estimates ====

it('estimates monthly revenue from active subscriptions only', function () {
    $monthly = $this->createPlan(['price' => 30, 'billing_interval' => 'monthly', 'trial_days' => 0]);
    $yearly = $this->createPlan(['price' => 120, 'billing_interval' => 'yearly', 'trial_days' => 0]);
    $trialing = $this->createPlan(['price' => 90, 'billing_interval' => 'monthly', 'trial_days' => 14]);

    $first = $this->createTenant(['plan_id' => $monthly->getKey()]);
    $second = $this->createTenant(['plan_id' => $yearly->getKey()]);
    $third = $this->createTenant(['plan_id' => $trialing->getKey()]);
    $subscriptions = app(SubscriptionService::class);

    $subscriptions->start($first, $monthly);
    $subscriptions->start($second, $yearly);
    $subscriptions->start($third, $trialing);

    expect(app(BillingService::class)->monthlyRecurringRevenue())->toBe(40.0);
});

it('stops resolving the storefront of a cancelled tenant', function () {
    $tenant = $this->createTenant();
    $other = $this->createTenant();
    $domains = app(DomainService::class);

    $cancelledHost = $domains->registerPlatformDomain($tenant)->hostname;
    $activeHost = $domains->registerPlatformDomain($other)->hostname;

    app(TenantManager::class)->cancel($tenant, 'Non-payment test');

    $resolver = app(TenantResolver::class);

    expect($tenant->refresh()->status)->toBe(TenantStatus::Cancelled)
        ->and($resolver->resolve($cancelledHost))->toBeNull()
        ->and($resolver->resolve($activeHost)?->getKey())->toBe($other->getKey());
});
