<?php

namespace Webkul\Saas\Http\Controllers\Tenant;

use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Services\EntitlementService;
use Webkul\Saas\Services\UsageService;

class UsageController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected EntitlementService $entitlements,
        protected UsageService $usage
    ) {}

    /**
     * Show live usage against the entitlements of the tenant plan.
     */
    public function index()
    {
        $tenant = $this->tenant()->load('plan');

        return view('saas::tenant.usage', [
            'tenant' => $tenant,
            'summary' => $this->entitlements->summary($tenant),
            'snapshot' => $this->usage->snapshot($tenant),
        ]);
    }
}
