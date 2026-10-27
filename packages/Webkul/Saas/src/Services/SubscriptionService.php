<?php

namespace Webkul\Saas\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Webkul\Saas\Contracts\Plan;
use Webkul\Saas\Contracts\Subscription;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Enums\SubscriptionStatus;
use Webkul\Saas\Repositories\SubscriptionRepository;

class SubscriptionService
{
    /**
     * Create a new subscription service.
     */
    public function __construct(
        protected SubscriptionRepository $subscriptions,
        protected PlatformSettings $settings,
        protected AuditLogger $audit
    ) {}

    /**
     * The subscription that currently governs a tenant.
     */
    public function current(Tenant $tenant): ?Subscription
    {
        if ($tenant->current_subscription_id) {
            $subscription = $this->subscriptions->find($tenant->current_subscription_id);

            if ($subscription) {
                return $subscription;
            }
        }

        return $this->subscriptions->currentForTenant($tenant->getKey());
    }

    /**
     * Start a subscription, honouring the plan trial period.
     */
    public function start(Tenant $tenant, Plan $plan, ?int $trialDays = null): Subscription
    {
        $existing = $this->current($tenant);

        if ($existing && (int) $existing->plan_id === (int) $plan->getKey() && $existing->status->grantsAccess()) {
            return $existing;
        }

        $trialDays = max(0, $trialDays ?? (int) $plan->trial_days);
        $isFree = (float) $plan->price <= 0;
        $now = now();

        $subscription = $this->subscriptions->create([
            'tenant_id' => $tenant->getKey(),
            'plan_id' => $plan->getKey(),
            'status' => (! $isFree && $trialDays > 0 ? SubscriptionStatus::Trialing : SubscriptionStatus::Active)->value,
            'starts_at' => $now,
            'trial_ends_at' => (! $isFree && $trialDays > 0) ? $now->copy()->addDays($trialDays) : null,
            'current_period_start' => $now,
            'current_period_end' => $this->periodEnd($plan, $now),
            'provider' => $isFree ? null : (string) config('saas.billing.provider'),
        ]);

        $this->attachToTenant($tenant, $subscription);

        $this->audit->forTenant($tenant, AuditAction::SubscriptionCreated, [
            'subject' => $subscription,
            'description' => 'Subscription started on the '.$plan->name.' plan.',
            'metadata' => [
                'plan' => $plan->slug,
                'status' => $subscription->status->value,
                'trial_days' => $trialDays,
            ],
        ]);

        return $subscription;
    }

    /**
     * Move a tenant to another plan while preserving history.
     */
    public function changePlan(Tenant $tenant, Plan $plan, ?int $trialDays = null): Subscription
    {
        $subscription = $this->current($tenant);

        if (! $subscription) {
            return $this->start($tenant, $plan, $trialDays);
        }

        $previousPlan = $subscription->plan?->slug;
        $now = now();

        $isFree = (float) $plan->price <= 0;
        $history = (array) ($subscription->metadata['plan_history'] ?? []);
        $history[] = [
            'from' => $previousPlan,
            'to' => $plan->slug,
            'changed_at' => $now->toIso8601String(),
        ];

        $subscription->fill([
            'plan_id' => $plan->getKey(),
            'status' => $isFree && $subscription->status === SubscriptionStatus::Trialing
                ? SubscriptionStatus::Active->value
                : $subscription->status->value,
            'current_period_start' => $now,
            'current_period_end' => $this->periodEnd($plan, $now),
            'metadata' => array_merge((array) $subscription->metadata, ['plan_history' => $history]),
        ])->save();

        $tenant->forceFill([
            'plan_id' => $plan->getKey(),
            'current_subscription_id' => $subscription->getKey(),
        ])->save();

        $this->audit->forTenant($tenant, AuditAction::SubscriptionPlanChanged, [
            'subject' => $subscription,
            'description' => 'Subscription plan changed.',
            'metadata' => ['from' => $previousPlan, 'to' => $plan->slug],
        ]);

        return $subscription;
    }

    /**
     * Extend an active trial with a recorded reason.
     */
    public function extendTrial(Tenant $tenant, int $days, string $reason): Subscription
    {
        if ($days < 1 || $days > 365) {
            throw ValidationException::withMessages([
                'days' => trans('saas::app.subscriptions.invalid-extension'),
            ]);
        }

        $subscription = $this->current($tenant);

        if (! $subscription || $subscription->status !== SubscriptionStatus::Trialing) {
            throw ValidationException::withMessages([
                'subscription' => trans('saas::app.subscriptions.not-trialing'),
            ]);
        }

        $base = $subscription->trial_ends_at?->isFuture() ? $subscription->trial_ends_at : now();

        $subscription->forceFill([
            'trial_ends_at' => $base->copy()->addDays($days),
        ])->save();

        $tenant->forceFill(['trial_ends_at' => $subscription->trial_ends_at])->save();

        $this->audit->forTenant($tenant, AuditAction::SubscriptionTrialExtended, [
            'subject' => $subscription,
            'description' => 'Trial extended by '.$days.' days.',
            'metadata' => ['days' => $days, 'reason' => $reason, 'ends_at' => $subscription->trial_ends_at?->toIso8601String()],
        ]);

        return $subscription;
    }

    /**
     * Cancel a subscription immediately or at the end of the paid period.
     */
    public function cancel(Tenant $tenant, bool $atPeriodEnd = true, ?string $reason = null): Subscription
    {
        $subscription = $this->current($tenant);

        if (! $subscription) {
            throw ValidationException::withMessages([
                'subscription' => trans('saas::app.subscriptions.not-found'),
            ]);
        }

        if (! $atPeriodEnd) {
            $this->transition($subscription, SubscriptionStatus::Cancelled, [
                'cancelled_at' => now(),
                'ends_at' => now(),
                'cancel_at_period_end' => false,
            ]);
        } else {
            $subscription->forceFill([
                'cancel_at_period_end' => true,
                'ends_at' => $subscription->current_period_end ?? now(),
            ])->save();
        }

        $this->audit->forTenant($tenant, AuditAction::SubscriptionCancelled, [
            'subject' => $subscription,
            'description' => $atPeriodEnd ? 'Subscription set to cancel at period end.' : 'Subscription cancelled.',
            'metadata' => ['reason' => $reason, 'at_period_end' => $atPeriodEnd],
        ]);

        return $subscription->refresh();
    }

    /**
     * Undo a pending cancellation or revive a cancelled subscription.
     */
    public function reactivate(Tenant $tenant): Subscription
    {
        $subscription = $this->current($tenant);

        if (! $subscription) {
            $plan = $tenant->plan;

            if (! $plan) {
                throw ValidationException::withMessages([
                    'subscription' => trans('saas::app.subscriptions.no-plan'),
                ]);
            }

            return $this->start($tenant, $plan);
        }

        if ($subscription->cancel_at_period_end && $subscription->status->grantsAccess()) {
            $subscription->forceFill(['cancel_at_period_end' => false, 'ends_at' => null])->save();
        } else {
            $now = now();

            $this->transition($subscription, SubscriptionStatus::Active, [
                'cancelled_at' => null,
                'cancel_at_period_end' => false,
                'ends_at' => null,
                'grace_ends_at' => null,
            ]);

            $subscription->forceFill([
                'current_period_start' => $now,
                'current_period_end' => $this->periodEnd($subscription->plan, $now),
            ])->save();
        }

        $this->audit->forTenant($tenant, AuditAction::SubscriptionReactivated, [
            'subject' => $subscription,
            'description' => 'Subscription reactivated.',
        ]);

        return $subscription->refresh();
    }

    /**
     * Suspend a subscription without ending it.
     */
    public function suspend(Tenant $tenant, ?string $reason = null): Subscription
    {
        $subscription = $this->current($tenant);

        if (! $subscription) {
            throw ValidationException::withMessages([
                'subscription' => trans('saas::app.subscriptions.not-found'),
            ]);
        }

        $this->transition($subscription, SubscriptionStatus::Suspended);

        $this->audit->forTenant($tenant, AuditAction::SubscriptionSuspended, [
            'subject' => $subscription,
            'description' => 'Subscription suspended.',
            'metadata' => ['reason' => $reason],
        ]);

        return $subscription->refresh();
    }

    /**
     * Resume a suspended subscription.
     */
    public function resume(Tenant $tenant): Subscription
    {
        $subscription = $this->current($tenant);

        if (! $subscription) {
            throw ValidationException::withMessages([
                'subscription' => trans('saas::app.subscriptions.not-found'),
            ]);
        }

        $this->transition($subscription, SubscriptionStatus::Active, ['grace_ends_at' => null]);

        return $subscription->refresh();
    }

    /**
     * Mark a subscription past due and open its grace period.
     */
    public function markPastDue(Subscription $subscription): Subscription
    {
        if ($subscription->status === SubscriptionStatus::PastDue) {
            return $subscription;
        }

        $this->transition($subscription, SubscriptionStatus::PastDue, [
            'grace_ends_at' => now()->addDays((int) config('saas.billing.grace_days', 3)),
        ]);

        $this->audit->forTenant($subscription->tenant, AuditAction::SubscriptionPastDue, [
            'subject' => $subscription,
            'description' => 'Subscription moved past due.',
        ]);

        return $subscription->refresh();
    }

    /**
     * Advance subscriptions whose billing dates have elapsed.
     */
    public function processLifecycle(): int
    {
        $processed = 0;

        foreach ($this->subscriptions->dueForLifecycle() as $subscription) {
            if ($subscription->status === SubscriptionStatus::Trialing && $subscription->trial_ends_at?->isPast()) {
                $this->handleTrialEnd($subscription);
                $processed++;

                continue;
            }

            if ($subscription->status === SubscriptionStatus::Active && $subscription->current_period_end?->isPast()) {
                $this->handlePeriodEnd($subscription);
                $processed++;

                continue;
            }

            if ($subscription->status === SubscriptionStatus::PastDue && $subscription->grace_ends_at?->isPast()) {
                $this->transition($subscription, SubscriptionStatus::Suspended);

                $this->audit->forTenant($subscription->tenant, AuditAction::SubscriptionSuspended, [
                    'subject' => $subscription,
                    'description' => 'Grace period ended without payment.',
                ]);

                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Apply the state a subscription takes when its trial expires.
     */
    protected function handleTrialEnd(Subscription $subscription): void
    {
        $plan = $subscription->plan;

        if ($plan && (float) $plan->price <= 0) {
            $now = now();

            $subscription->forceFill([
                'status' => SubscriptionStatus::Active->value,
                'trial_ends_at' => null,
                'current_period_start' => $now,
                'current_period_end' => $this->periodEnd($plan, $now),
            ])->save();

            return;
        }

        $this->markPastDue($subscription);
    }

    /**
     * Renew a paid subscription or expire it after the period ends.
     */
    protected function handlePeriodEnd(Subscription $subscription): void
    {
        if ($subscription->cancel_at_period_end) {
            $this->transition($subscription, SubscriptionStatus::Cancelled, ['cancelled_at' => now()]);

            $this->audit->forTenant($subscription->tenant, AuditAction::SubscriptionCancelled, [
                'subject' => $subscription,
                'description' => 'Subscription cancelled at period end.',
            ]);

            return;
        }

        $plan = $subscription->plan;

        if (! $plan) {
            return;
        }

        $now = now();

        if ((float) $plan->price <= 0) {
            $subscription->forceFill([
                'current_period_start' => $now,
                'current_period_end' => $this->periodEnd($plan, $now),
            ])->save();

            return;
        }

        $this->markPastDue($subscription);
    }

    /**
     * Apply a status transition after validating it.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function transition(Subscription $subscription, SubscriptionStatus $target, array $attributes = []): void
    {
        if (! $subscription->status->canTransitionTo($target)) {
            throw ValidationException::withMessages([
                'status' => trans('saas::app.subscriptions.invalid-transition', [
                    'from' => $subscription->status->label(),
                    'to' => $target->label(),
                ]),
            ]);
        }

        $subscription->forceFill(array_merge($attributes, ['status' => $target->value]))->save();
    }

    /**
     * Keep the tenant's billing pointers in step with its subscription.
     */
    protected function attachToTenant(Tenant $tenant, Subscription $subscription): void
    {
        $tenant->forceFill([
            'plan_id' => $subscription->plan_id,
            'current_subscription_id' => $subscription->getKey(),
            'trial_ends_at' => $subscription->trial_ends_at,
        ])->save();
    }

    /**
     * Move a subscription to the period that follows the one just invoiced.
     */
    public function advancePeriod(Subscription $subscription, CarbonInterface $periodEnd): void
    {
        $plan = $subscription->plan;

        if (! $plan) {
            return;
        }

        $start = Carbon::instance($periodEnd);

        $subscription->forceFill([
            'current_period_start' => $start,
            'current_period_end' => $this->periodEnd($plan, $start),
        ])->save();
    }

    /**
     * Calculate the end of a billing period from a start date.
     */
    public function periodEnd(?Plan $plan, CarbonInterface $start): Carbon
    {
        $start = Carbon::instance($start);

        return match ($plan?->billing_interval) {
            'yearly' => $start->copy()->addYear(),
            'quarterly' => $start->copy()->addMonths(3),
            default => $start->copy()->addMonth(),
        };
    }
}
