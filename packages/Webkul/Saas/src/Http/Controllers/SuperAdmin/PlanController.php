<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Enums\FeatureKey;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Services\AuditLogger;

class PlanController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PlanRepository $plans,
        protected AuditLogger $audit
    ) {}

    /**
     * List every plan with its subscriber count.
     */
    public function index()
    {
        return view('saas::super-admin.plans.index', [
            'plans' => $this->plans->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Show the plan creation form.
     */
    public function create()
    {
        return view('saas::super-admin.plans.form', [
            'plan' => null,
            'features' => FeatureKey::cases(),
        ]);
    }

    /**
     * Store a new plan.
     */
    public function store(Request $request)
    {
        $data = $this->validatePlan($request);

        $plan = $this->plans->create($this->payload($data));

        $this->audit->platform(AuditAction::PlanCreated, [
            'subject' => $plan,
            'description' => 'Plan created.',
            'metadata' => ['slug' => $plan->slug],
        ]);

        return redirect()
            ->route('saas.super-admin.plans.index')
            ->with('success', trans('saas::app.super-admin.plans.created'));
    }

    /**
     * Preview a plan as tenants will see it.
     */
    public function show(int $plan)
    {
        return view('saas::super-admin.plans.show', [
            'plan' => $this->plans->findOrFail($plan),
            'features' => FeatureKey::cases(),
        ]);
    }

    /**
     * Show the plan editing form.
     */
    public function edit(int $plan)
    {
        return view('saas::super-admin.plans.form', [
            'plan' => $this->plans->findOrFail($plan),
            'features' => FeatureKey::cases(),
        ]);
    }

    /**
     * Update an existing plan.
     */
    public function update(Request $request, int $plan)
    {
        $plan = $this->plans->findOrFail($plan);

        $data = $this->validatePlan($request, $plan->getKey());

        $this->plans->update($this->payload($data), $plan->getKey());

        $this->audit->platform(AuditAction::PlanUpdated, [
            'subject' => $plan,
            'description' => 'Plan updated.',
            'metadata' => ['slug' => $data['slug']],
        ]);

        return redirect()
            ->route('saas.super-admin.plans.index')
            ->with('success', trans('saas::app.super-admin.plans.updated'));
    }

    /**
     * Remove a plan that has never been purchased.
     */
    public function destroy(int $plan)
    {
        $plan = $this->plans->findOrFail($plan);

        if ($this->plans->hasSubscribers($plan->getKey())) {
            return back()->with('error', trans('saas::app.super-admin.plans.in-use'));
        }

        $this->plans->delete($plan->getKey());

        $this->audit->platform(AuditAction::PlanDeleted, [
            'description' => 'Plan deleted.',
            'metadata' => ['slug' => $plan->slug],
        ]);

        return redirect()
            ->route('saas.super-admin.plans.index')
            ->with('success', trans('saas::app.super-admin.plans.deleted'));
    }

    /**
     * Validate a plan submission.
     *
     * @return array<string, mixed>
     */
    protected function validatePlan(Request $request, ?int $ignoreId = null): array
    {
        $unique = Rule::unique('saas_plans', 'slug');

        if ($ignoreId) {
            $unique = $unique->ignore($ignoreId);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $unique],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'yearly_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_interval' => ['required', Rule::in(['monthly', 'quarterly', 'yearly'])],
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
            'premium_themes' => ['nullable', 'integer', 'min:0', 'max:999'],
            'offer_status' => ['nullable', Rule::in(['inactive', 'active'])],
            'offer_title' => ['nullable', 'string', 'max:120'],
            'offer_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'offer_discount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'limits' => ['nullable', 'array'],
            'limits.*' => ['nullable', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'boolean'],
        ]);

        return $data;
    }

    /**
     * Normalise validated input into persisted attributes.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function payload(array $data): array
    {
        $limits = [];
        $features = [];

        foreach (FeatureKey::cases() as $feature) {
            if ($feature->isQuota()) {
                $value = $data['limits'][$feature->value] ?? null;

                $limits[$feature->value] = ($value === null || $value === '')
                    ? null
                    : max(0, (int) $value);

                continue;
            }

            $features[$feature->value] = (bool) ($data['features'][$feature->value] ?? false);
        }

        foreach (['max_categories', 'max_attributes', 'max_attribute_families', 'max_channels'] as $quota) {
            $value = $data['limits'][$quota] ?? null;

            $limits[$quota] = ($value === null || $value === '')
                ? null
                : max(0, (int) $value);
        }

        return [
            'name' => $data['name'],
            'slug' => strtolower($data['slug']),
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'yearly_price' => $data['yearly_price'] ?? null,
            'currency' => strtoupper($data['currency']),
            'billing_interval' => $data['billing_interval'],
            'trial_days' => (int) $data['trial_days'],
            'premium_themes' => (int) ($data['premium_themes'] ?? 0),
            'offer_status' => $data['offer_status'] ?? 'inactive',
            'offer_title' => $data['offer_title'] ?? null,
            'offer_type' => $data['offer_type'] ?? 'percentage',
            'offer_discount' => $data['offer_discount'] ?? null,
            'limits' => $limits,
            'features' => $features,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_public' => (bool) ($data['is_public'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
