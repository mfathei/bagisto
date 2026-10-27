<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Repositories\SubscriptionRepository;
use Webkul\Saas\Services\SubscriptionService;

class SubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SubscriptionRepository $subscriptions,
        protected PlanRepository $plans,
        protected SubscriptionService $service
    ) {}

    /**
     * List subscriptions with filters.
     */
    public function index(Request $request)
    {
        return view('saas::super-admin.subscriptions.index', [
            'subscriptions' => $this->subscriptions->paginateFiltered($request->only(['status', 'plan_id'])),
            'plans' => $this->plans->orderBy('sort_order')->get(),
            'filters' => $request->only(['status', 'plan_id']),
        ]);
    }

    /**
     * Show one subscription with its billing history.
     */
    public function show(int $subscription)
    {
        $subscription = $this->subscriptions->findOrFail($subscription);

        $subscription->load(['tenant', 'plan', 'billingRecords']);

        return view('saas::super-admin.subscriptions.show', [
            'subscription' => $subscription,
            'plans' => $this->plans->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Extend the running trial with an audited reason.
     */
    public function extendTrial(Request $request, int $subscription)
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $subscription = $this->subscriptions->findOrFail($subscription);

        $this->service->extendTrial($subscription->tenant, (int) $data['days'], $data['reason']);

        return back()->with('success', trans('saas::app.super-admin.subscriptions.trial-extended'));
    }

    /**
     * Move the subscription to another plan.
     */
    public function changePlan(Request $request, int $subscription)
    {
        $data = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:saas_plans,id'],
        ]);

        $subscription = $this->subscriptions->findOrFail($subscription);

        $plan = $this->plans->findOrFail($data['plan_id']);

        $this->service->changePlan($subscription->tenant, $plan);

        return back()->with('success', trans('saas::app.super-admin.subscriptions.plan-changed'));
    }

    /**
     * Cancel the subscription now or at the end of the period.
     */
    public function cancel(Request $request, int $subscription)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
            'immediate' => ['nullable', 'boolean'],
        ]);

        $subscription = $this->subscriptions->findOrFail($subscription);

        $this->service->cancel(
            $subscription->tenant,
            ! (bool) ($data['immediate'] ?? false),
            $data['reason'] ?? null
        );

        return back()->with('success', trans('saas::app.super-admin.subscriptions.cancelled'));
    }

    /**
     * Reactivate a subscription pending cancellation.
     */
    public function reactivate(int $subscription)
    {
        $subscription = $this->subscriptions->findOrFail($subscription);

        $this->service->reactivate($subscription->tenant);

        return back()->with('success', trans('saas::app.super-admin.subscriptions.reactivated'));
    }
}
