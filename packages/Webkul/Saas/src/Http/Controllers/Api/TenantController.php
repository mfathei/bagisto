<?php

namespace Webkul\Saas\Http\Controllers\Api;

use Webkul\Saas\Enums\DomainStatus;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Services\EntitlementService;
use Webkul\Saas\Services\UsageService;

class TenantController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected EntitlementService $entitlements,
        protected UsageService $usage
    ) {}

    /**
     * Describe the authenticated tenant store.
     */
    public function show()
    {
        $tenant = $this->tenant()->load(['plan', 'currentSubscription.plan', 'domains']);

        $domains = $tenant->domains
            ->where('status', DomainStatus::Verified)
            ->map(fn ($domain) => [
                'hostname' => $domain->hostname,
                'type' => $domain->type->value,
                'is_primary' => (bool) $domain->is_primary,
            ])
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'uuid' => $tenant->uuid,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'status' => $tenant->status->value,
                'locale' => $tenant->default_locale,
                'currency' => $tenant->default_currency,
                'timezone' => $tenant->timezone,
                'plan' => $tenant->plan ? [
                    'slug' => $tenant->plan->slug,
                    'name' => $tenant->plan->name,
                ] : null,
                'subscription' => $tenant->currentSubscription ? [
                    'status' => $tenant->currentSubscription->status->value,
                    'trial_ends_at' => $tenant->currentSubscription->trial_ends_at?->toIso8601String(),
                    'current_period_end' => $tenant->currentSubscription->current_period_end?->toIso8601String(),
                ] : null,
                'domains' => $domains,
                'storefront_url' => $tenant->storefrontUrl(),
            ],
        ]);
    }

    /**
     * Report usage against the tenant entitlements.
     */
    public function usage()
    {
        $tenant = $this->tenant()->load('plan');

        return response()->json([
            'data' => [
                'usage' => $this->usage->snapshot($tenant),
                'entitlements' => $this->entitlements->summary($tenant),
            ],
        ]);
    }
}
