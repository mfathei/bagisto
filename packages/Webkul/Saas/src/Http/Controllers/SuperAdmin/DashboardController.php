<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Support\Carbon;
use Webkul\Saas\Enums\SubscriptionStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\AuditLogRepository;
use Webkul\Saas\Repositories\BillingRecordRepository;
use Webkul\Saas\Repositories\PaymentTransactionRepository;
use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Repositories\ProvisioningOperationRepository;
use Webkul\Saas\Repositories\SubscriptionRepository;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Services\BillingService;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TenantRepository $tenants,
        protected SubscriptionRepository $subscriptions,
        protected PlanRepository $plans,
        protected ProvisioningOperationRepository $operations,
        protected AuditLogRepository $audit,
        protected BillingService $billing,
        protected BillingRecordRepository $invoices,
        protected PaymentTransactionRepository $transactions
    ) {}

    /**
     * Show the platform dashboard built from live records.
     */
    public function index()
    {
        [$from, $to] = $this->dateRange();

        $rangeDays = max(0, $from->diffInDays($to));

        $previousTo = $from->copy()->subDay()->endOfDay();

        $previousFrom = $previousTo->copy()->subDays($rangeDays)->startOfDay();

        $statusCounts = $this->tenants->countsByStatus();

        $signupDates = $this->tenants->createdAtBetween($from, $to);

        $previousSignups = $this->tenants->createdBetween($previousFrom, $previousTo);

        $payments = $this->transactions->succeededBetween($from, $to);

        $totalRevenue = $this->billing->collectedRevenue($from, $to);

        $previousRevenue = $this->billing->collectedRevenue($previousFrom, $previousTo);

        $planCounts = $this->subscriptions->countsByPlan([
            SubscriptionStatus::Trialing->value,
            SubscriptionStatus::Active->value,
            SubscriptionStatus::PastDue->value,
        ]);

        $plans = $this->plans->all()->map(fn ($plan) => [
            'name' => $plan->name,
            'count' => (int) ($planCounts[$plan->getKey()] ?? 0),
        ])->filter(fn ($row) => $row['count'] > 0)->values();

        return view('saas::super-admin.dashboard', [
            'from' => $from,
            'to' => $to,
            'statusCounts' => $statusCounts,
            'totalTenants' => array_sum($statusCounts),
            'activeTenants' => $statusCounts[TenantStatus::Active->value] ?? 0,
            'activeSubscriptions' => $this->subscriptions->countByStatus(SubscriptionStatus::Active->value),
            'pastDueSubscriptions' => $this->subscriptions->countByStatus(SubscriptionStatus::PastDue->value),
            'trialingSubscriptions' => $this->subscriptions->countByStatus(SubscriptionStatus::Trialing->value),
            'totalRevenue' => $totalRevenue,
            'dueAmount' => $this->invoices->outstandingBalance(),
            'tenantGrowth' => $this->growth($previousSignups, $signupDates->count()),
            'revenueGrowth' => $this->growth($previousRevenue, $totalRevenue),
            'recentTenants' => $this->tenants->latest(5),
            'recentInvoices' => $this->invoices->recent(5),
            'expiringSoon' => $this->subscriptions->expiringWithin(14, 5),
            'signupsPerDay' => $this->bucketCounts($signupDates, $from, $to, $rangeDays),
            'revenuePerDay' => $this->bucketAmounts($payments, $from, $to, $rangeDays),
            'planCounts' => $plans,
            'failedOperations' => $this->operations->failed(),
            'recentAudit' => $this->audit->recent(10),
        ]);
    }

    /**
     * Resolve the selected date range, defaulting to the last 30 days.
     */
    protected function dateRange(): array
    {
        $from = request()->date('from');

        $to = request()->date('to');

        if (! $from || ! $to || $from->greaterThan($to)) {
            $to = now()->endOfDay();

            $from = now()->subDays(29)->startOfDay();
        } else {
            $from = $from->startOfDay();

            $to = $to->endOfDay();
        }

        return [$from, $to];
    }

    /**
     * Percentage change against the previous period, or null when undefined.
     */
    protected function growth(float|int $previous, float|int $current): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Registration counts grouped into day or week buckets for the whole range.
     */
    protected function bucketCounts($dates, Carbon $from, Carbon $to, int $rangeDays)
    {
        $step = $rangeDays > 62 ? 7 : 1;

        $buckets = $this->emptyBuckets($from, $to, $step);

        foreach ($dates as $date) {
            $key = $this->bucketKey($date, $from, $step);

            if (isset($buckets[$key])) {
                $buckets[$key]['value']++;
            }
        }

        return array_values($buckets);
    }

    /**
     * Payment amounts grouped into day or week buckets for the whole range.
     */
    protected function bucketAmounts($payments, Carbon $from, Carbon $to, int $rangeDays)
    {
        $step = $rangeDays > 62 ? 7 : 1;

        $buckets = $this->emptyBuckets($from, $to, $step);

        foreach ($payments as $payment) {
            $key = $this->bucketKey($payment->processed_at, $from, $step);

            if (isset($buckets[$key])) {
                $buckets[$key]['value'] += (float) $payment->amount;
            }
        }

        return array_values($buckets);
    }

    /**
     * Zero-filled buckets labelled by their first day.
     */
    protected function emptyBuckets(Carbon $from, Carbon $to, int $step): array
    {
        $buckets = [];

        $cursor = $from->copy();

        while ($cursor->lessThanOrEqualTo($to)) {
            $buckets[$cursor->toDateString()] = [
                'label' => $cursor->format('d M'),
                'value' => 0,
            ];

            $cursor->addDays($step);
        }

        return $buckets;
    }

    /**
     * The bucket key holding a given date.
     */
    protected function bucketKey(Carbon $date, Carbon $from, int $step): string
    {
        $index = (int) floor($from->diffInDays($date->copy()->startOfDay()) / $step);

        return $from->copy()->addDays($index * $step)->toDateString();
    }
}
