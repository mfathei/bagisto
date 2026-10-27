<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Support\Carbon;
use Webkul\Saas\Enums\SubscriptionStatus;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\BillingRecordRepository;
use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Repositories\SubscriptionRepository;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Services\BillingService;
use Webkul\Saas\Services\TenantStatistics;

class InsightsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TenantRepository $tenants,
        protected SubscriptionRepository $subscriptions,
        protected PlanRepository $plans,
        protected BillingRecordRepository $invoices,
        protected BillingService $billing,
        protected TenantStatistics $statistics
    ) {}

    /**
     * Show platform growth, revenue and catalogue figures.
     */
    public function index()
    {
        $statusCounts = $this->tenants->countsByStatus();

        $activeSubscriptions = $this->subscriptions->getModel()
            ->newQuery()
            ->with('plan')
            ->where('status', SubscriptionStatus::Active->value)
            ->get();

        $mrr = round($activeSubscriptions->sum(fn ($subscription) => (float) ($subscription->plan?->monthlyPrice() ?? 0)), 2);

        $planCounts = $this->subscriptions->countsByPlan([
            SubscriptionStatus::Trialing->value,
            SubscriptionStatus::Active->value,
            SubscriptionStatus::PastDue->value,
        ]);

        $planDistribution = $this->plans->all()
            ->map(fn ($plan) => [
                'name' => $plan->name,
                'count' => (int) ($planCounts[$plan->getKey()] ?? 0),
            ])
            ->sortByDesc('count')
            ->values();

        return view('saas::super-admin.insights.index', [
            'statusCounts' => $statusCounts,
            'activeSubscriptions' => $activeSubscriptions->count(),
            'trialingSubscriptions' => $this->subscriptions->countByStatus(SubscriptionStatus::Trialing->value),
            'mrr' => $mrr,
            'arr' => round($mrr * 12, 2),
            'collected' => $this->billing->collectedRevenue(now()->subDays(29)->startOfDay(), now()),
            'outstanding' => $this->invoices->outstandingBalance(),
            'platformTotals' => $this->statistics->platformTotals(),
            'signups' => $this->monthlySignups(),
            'planDistribution' => $planDistribution,
            'recentSubscriptions' => $this->subscriptions->getModel()
                ->newQuery()
                ->with(['tenant', 'plan'])
                ->orderByDesc('created_at')
                ->limit(6)
                ->get(),
        ]);
    }

    /**
     * Tenant registrations for the last six months, labelled by month.
     *
     * @return array<int, array{label: string, value: int}>
     */
    protected function monthlySignups(): array
    {
        $months = [];

        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $start = Carbon::now()->startOfMonth()->subMonths($monthsAgo);

            $months[] = [
                'label' => $start->format('M'),
                'value' => $this->tenants->createdBetween($start->copy(), $start->copy()->endOfMonth()),
            ];
        }

        return $months;
    }
}
