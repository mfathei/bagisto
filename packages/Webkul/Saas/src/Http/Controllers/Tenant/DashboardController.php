<?php

namespace Webkul\Saas\Http\Controllers\Tenant;

use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\DomainStatus;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\AuditLogRepository;
use Webkul\Saas\Services\EntitlementService;
use Webkul\Saas\Services\UsageService;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected EntitlementService $entitlements,
        protected UsageService $usage,
        protected AuditLogRepository $audit
    ) {}

    /**
     * Show the tenant dashboard with usage, subscription and onboarding state.
     */
    public function index()
    {
        $tenant = $this->tenant()->load(['plan', 'currentSubscription.plan', 'domains']);

        $usage = $this->usage->snapshot($tenant);

        return view('saas::tenant.dashboard', [
            'tenant' => $tenant,
            'membership' => $this->membership(),
            'usage' => $usage,
            'summary' => $this->entitlements->summary($tenant),
            'onboarding' => $this->onboarding($tenant, $usage),
            'recentAudit' => $this->audit->recent(10, $tenant->getKey()),
        ]);
    }

    /**
     * The onboarding checklist, deriving the steps the platform can detect itself.
     *
     * @param  array<string, float>  $usage
     * @return array<int, array{key: string, label: string, completed: bool}>
     */
    protected function onboarding(Tenant $tenant, array $usage): array
    {
        $settings = (array) $tenant->settings;

        $stored = (array) ($settings['onboarding'] ?? []);

        $checks = [
            'profile_completed' => (bool) ($stored['profile_completed'] ?? false),
            'branding_completed' => (bool) ($stored['branding_completed'] ?? false),
            'domain_completed' => $tenant->domains->contains(
                fn ($domain) => $domain->status === DomainStatus::Verified
            ),
            'product_created' => ($usage['products'] ?? 0) > 0,
            'payment_configured' => (bool) ($stored['payment_configured'] ?? false),
            'shipping_configured' => (bool) ($stored['shipping_configured'] ?? false),
            'published' => (bool) ($stored['published'] ?? false),
        ];

        return array_map(
            fn (string $key, bool $completed) => [
                'key' => $key,
                'label' => trans('saas::app.tenant.onboarding.steps.'.$key),
                'completed' => $completed,
            ],
            array_keys($checks),
            array_values($checks)
        );
    }
}
