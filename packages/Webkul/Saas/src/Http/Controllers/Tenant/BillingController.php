<?php

namespace Webkul\Saas\Http\Controllers\Tenant;

use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\BillingRecordRepository;

class BillingController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected BillingRecordRepository $invoices) {}

    /**
     * Show the tenant subscription and its billing history.
     */
    public function index()
    {
        $this->authorizeAdministrative();

        $tenant = $this->tenant()->load(['plan', 'currentSubscription.plan']);

        $records = $this->invoices
            ->with(['subscription.plan', 'transactions'])
            ->where('tenant_id', $tenant->getKey())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('saas::tenant.billing', [
            'tenant' => $tenant,
            'subscription' => $tenant->currentSubscription,
            'records' => $records,
            'outstanding' => $this->invoices->outstandingForTenant($tenant->getKey()),
        ]);
    }
}
