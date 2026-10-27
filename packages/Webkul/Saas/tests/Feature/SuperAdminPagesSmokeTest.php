<?php

use Webkul\Saas\Enums\BillingStatus;
use Webkul\Saas\Models\BillingRecord;
use Webkul\Saas\Models\SuperAdmin;
use Webkul\Saas\Services\SubscriptionService;

// ==== Super Admin Pages ====

it('renders every super admin page', function () {
    $admin = SuperAdmin::create([
        'name' => 'Smoke Tester',
        'email' => 'smoke-'.Str::lower(Str::random(8)).'@example.com',
        'password' => bcrypt('password'),
        'role' => 'owner',
        'status' => true,
    ]);

    $this->actingAs($admin, 'super_admin');

    $plan = $this->createPlan();
    $tenant = $this->createTenant(['plan_id' => $plan->getKey()]);
    $subscription = app(SubscriptionService::class)->start($tenant, $plan);

    $invoice = BillingRecord::create([
        'tenant_id' => $tenant->getKey(),
        'subscription_id' => $subscription->getKey(),
        'number' => 'INV-'.Str::upper(Str::random(6)),
        'status' => BillingStatus::Open->value,
        'currency' => 'USD',
        'subtotal' => 20,
        'tax' => 0,
        'total' => 20,
        'amount_paid' => 0,
        'period_start' => now()->subMonth(),
        'period_end' => now(),
        'due_at' => now()->addWeek(),
    ]);

    $routes = [
        route('saas.super-admin.dashboard'),
        route('saas.super-admin.tenants.index'),
        route('saas.super-admin.tenants.orders'),
        route('saas.super-admin.tenants.products'),
        route('saas.super-admin.tenants.customers'),
        route('saas.super-admin.tenants.create'),
        route('saas.super-admin.tenants.show', $tenant->getKey()),
        route('saas.super-admin.tenants.edit', $tenant->getKey()),
        route('saas.super-admin.plans.index'),
        route('saas.super-admin.plans.create'),
        route('saas.super-admin.plans.show', $plan->getKey()),
        route('saas.super-admin.plans.edit', $plan->getKey()),
        route('saas.super-admin.subscriptions.index'),
        route('saas.super-admin.subscriptions.show', $subscription->getKey()),
        route('saas.super-admin.billing.index'),
        route('saas.super-admin.billing.show', $invoice->getKey()),
        route('saas.super-admin.domains.index'),
        route('saas.super-admin.provisioning.index'),
        route('saas.super-admin.audit.index'),
        route('saas.super-admin.settings.index'),
        route('saas.super-admin.cms.index'),
        route('saas.super-admin.insights.index'),
        route('saas.super-admin.configure.index'),
    ];

    foreach ($routes as $url) {
        $this->get($url)->assertOk();
    }
});
