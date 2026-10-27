<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\Subscription;
use Webkul\Saas\Enums\SubscriptionStatus;

class SubscriptionRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Subscription::class;
    }

    /**
     * The current subscription of a tenant.
     */
    public function currentForTenant(int $tenantId): ?Subscription
    {
        return $this->model
            ->with('plan')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [
                SubscriptionStatus::Trialing->value,
                SubscriptionStatus::Active->value,
                SubscriptionStatus::PastDue->value,
            ])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Subscriptions that need lifecycle evaluation.
     */
    public function dueForLifecycle()
    {
        return $this->model
            ->with(['tenant', 'plan'])
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('status', SubscriptionStatus::Trialing->value)
                        ->whereNotNull('trial_ends_at')
                        ->where('trial_ends_at', '<=', now());
                })->orWhere(function ($query) {
                    $query->where('status', SubscriptionStatus::Active->value)
                        ->whereNotNull('current_period_end')
                        ->where('current_period_end', '<=', now());
                })->orWhere(function ($query) {
                    $query->where('status', SubscriptionStatus::PastDue->value)
                        ->whereNotNull('grace_ends_at')
                        ->where('grace_ends_at', '<=', now());
                });
            })
            ->get();
    }

    /**
     * Filtered listing for the Super Admin panel.
     */
    public function paginateFiltered(array $filters, int $perPage = 20)
    {
        return $this->model
            ->with(['tenant', 'plan'])
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['plan_id']), fn ($query) => $query->where('plan_id', $filters['plan_id']))
            ->when(! empty($filters['tenant_id']), fn ($query) => $query->where('tenant_id', $filters['tenant_id']))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Count subscriptions matching one or more statuses.
     */
    public function countByStatus(string|array $statuses): int
    {
        return $this->model
            ->whereIn('status', (array) $statuses)
            ->count();
    }

    /**
     * Count subscriptions per plan for the given statuses.
     *
     * @return array<int, int>
     */
    public function countsByPlan(array $statuses): array
    {
        return $this->model
            ->whereIn('status', $statuses)
            ->selectRaw('plan_id, count(*) as aggregate')
            ->groupBy('plan_id')
            ->pluck('aggregate', 'plan_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    /**
     * Subscriptions whose trial or current period ends within the coming days.
     */
    public function expiringWithin(int $days, int $limit = 5)
    {
        $now = now();

        $until = $now->copy()->addDays($days);

        return $this->model->newQuery()
            ->with(['tenant', 'plan'])
            ->whereIn('status', [SubscriptionStatus::Trialing->value, SubscriptionStatus::Active->value])
            ->where(function ($query) use ($now, $until) {
                $query->whereBetween('trial_ends_at', [$now, $until])
                    ->orWhereBetween('current_period_end', [$now, $until]);
            })
            ->limit($limit * 4)
            ->get()
            ->sortBy(fn ($subscription) => $this->nextEndingDate($subscription))
            ->take($limit)
            ->values();
    }

    /**
     * The nearest upcoming end date of a subscription, trial or period.
     */
    protected function nextEndingDate($subscription)
    {
        $trial = $subscription->trial_ends_at;

        $period = $subscription->current_period_end;

        if ($trial && $period) {
            return $trial->min($period);
        }

        return $trial ?? $period;
    }
}
