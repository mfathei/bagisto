<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\Plan;

class PlanRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Plan::class;
    }

    /**
     * Find a plan by its public slug.
     */
    public function findBySlug(string $slug): ?Plan
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Plans that may be shown on the public registration page.
     */
    public function publicPlans()
    {
        return $this->model
            ->where('is_active', true)
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
    }

    /**
     * The fallback plan used when a tenant does not choose one.
     */
    public function defaultPlan(): ?Plan
    {
        return $this->findBySlug((string) config('saas.registration.default_plan'))
            ?: $this->model->where('is_active', true)->orderBy('sort_order')->first();
    }

    /**
     * Whether a plan has ever been subscribed to.
     */
    public function hasSubscribers(int $planId): bool
    {
        return $this->model->newQuery()
            ->whereKey($planId)
            ->whereHas('subscriptions')
            ->exists();
    }
}
