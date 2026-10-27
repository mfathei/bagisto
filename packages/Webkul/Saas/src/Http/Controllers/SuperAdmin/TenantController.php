<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\AuditLogRepository;
use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Repositories\ProvisioningOperationRepository;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Repositories\UsageMetricRepository;
use Webkul\Saas\Services\AuditLogger;
use Webkul\Saas\Services\ProvisioningService;
use Webkul\Saas\Services\TenantManager;
use Webkul\Saas\Services\TenantStatistics;

class TenantController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TenantRepository $tenants,
        protected PlanRepository $plans,
        protected ProvisioningOperationRepository $operations,
        protected UsageMetricRepository $metrics,
        protected AuditLogRepository $audit,
        protected TenantManager $manager,
        protected ProvisioningService $provisioning,
        protected AuditLogger $logger,
        protected TenantStatistics $statistics
    ) {}

    /**
     * List tenants with filters.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'plan_id', 'from', 'to', 'sort', 'direction']);

        $perPage = max(10, min(100, (int) $request->input('per_page', 20)));

        return view('saas::super-admin.tenants.index', [
            'tenants' => $this->tenants->paginateFiltered($filters, $perPage),
            'plans' => $this->plans->orderBy('sort_order')->get(),
            'filters' => $filters,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the tenant creation form.
     */
    public function create()
    {
        return view('saas::super-admin.tenants.create', [
            'plans' => $this->plans->orderBy('sort_order')->get(),
            'timezones' => DateTimeZone::listIdentifiers(),
            'locales' => core()->getAllLocales(),
            'currencies' => core()->getAllCurrencies(),
        ]);
    }

    /**
     * Create a pre-approved tenant and start provisioning.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:30', 'regex:'.config('saas.tenants.slug_regex')],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:40'],
            'plan_id' => ['required', 'integer', 'exists:saas_plans,id'],
            'owner_name' => ['required', 'string', 'max:150'],
            'owner_email' => ['required', 'email', 'max:191'],
            'owner_password' => ['required', 'string', 'min:'.config('saas.registration.password_min_length', 12), 'confirmed'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:12'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $result = $this->manager->create(array_merge($data, [
            'email_verified' => true,
            'approved' => true,
        ]));

        $this->manager->activate($result['tenant']);

        return redirect()
            ->route('saas.super-admin.tenants.show', $result['tenant'])
            ->with('success', trans('saas::app.super-admin.tenants.created'));
    }

    /**
     * Show a tenant with its lifecycle, billing and provisioning records.
     */
    public function show(int $tenant)
    {
        $tenant = $this->tenants->findOrFail($tenant);

        $tenant->load([
            'plan',
            'currentSubscription.plan',
            'domains',
            'subscriptions.plan',
            'users.admin',
            'billingRecords',
            'provisioningOperations',
            'entitlements',
        ]);

        return view('saas::super-admin.tenants.show', [
            'tenant' => $tenant,
            'usage' => $this->metrics->allForTenant($tenant->getKey()),
            'recentAudit' => $this->audit->recent(15, $tenant->getKey()),
            'operations' => $tenant->provisioningOperations->sortByDesc('id'),
            'statistics' => $this->statistics->forTenant($tenant),
            'plans' => $this->plans->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Show the tenant profile form.
     */
    public function edit(int $tenant)
    {
        $tenant = $this->tenants->findOrFail($tenant);

        $tenant->load(['plan', 'currentSubscription.plan', 'domains', 'owner']);

        return view('saas::super-admin.tenants.edit', [
            'tenant' => $tenant,
            'plans' => $this->plans->orderBy('sort_order')->get(),
            'timezones' => DateTimeZone::listIdentifiers(),
            'locales' => core()->getAllLocales(),
            'currencies' => core()->getAllCurrencies(),
        ]);
    }

    /**
     * Update the profile and settings of a tenant.
     */
    public function update(Request $request, int $tenant)
    {
        $tenant = $this->tenants->findOrFail($tenant);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:75'],
            'last_name' => ['required', 'string', 'max:75'],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required',
                'string',
                'max:30',
                'regex:'.config('saas.tenants.slug_regex'),
                Rule::unique('saas_tenants', 'slug')->ignore($tenant->getKey())->whereNull('deleted_at'),
            ],
            'cname' => ['nullable', 'string', 'max:150', 'regex:/^[a-zA-Z0-9.-]+$/'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:12'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        if ($request->hasFile('logo')) {
            if ($tenant->logo) {
                Storage::delete($tenant->logo);
            }

            $data['logo'] = $request->file('logo')->store('tenants/'.$tenant->getKey(), 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($tenant->logo) {
                Storage::delete($tenant->logo);
            }

            $data['logo'] = null;
        }

        $data['owner_name'] = trim($data['first_name'].' '.$data['last_name']);

        $this->manager->update($tenant, $data);

        $tenant->refresh();

        $activate = $request->boolean('is_active');

        if ($activate && $tenant->status !== TenantStatus::Active) {
            if ($tenant->status === TenantStatus::Suspended) {
                $this->manager->reactivate($tenant);
            } elseif ($tenant->status !== TenantStatus::Cancelled) {
                $this->manager->activate($tenant);
            }
        } elseif (! $activate && $tenant->status === TenantStatus::Active) {
            $this->manager->suspend($tenant, 'Suspended from the tenant profile.');
        }

        return redirect()
            ->route('saas.super-admin.tenants.show', $tenant->getKey())
            ->with('success', trans('saas::app.super-admin.tenants.updated'));
    }

    /**
     * Assign a subscription plan to a tenant.
     */
    public function assignPlan(Request $request, int $tenant)
    {
        $tenant = $this->tenants->findOrFail($tenant);

        $data = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:saas_plans,id'],
        ]);

        $this->manager->assignPlan($tenant, $this->plans->findOrFail($data['plan_id']));

        return back()->with('success', trans('saas::app.super-admin.tenants.plan-assigned'));
    }

    /**
     * Approve a registration awaiting review.
     */
    public function approve(int $tenant)
    {
        $this->manager->approve($this->tenants->findOrFail($tenant));

        return back()->with('success', trans('saas::app.super-admin.tenants.approved'));
    }

    /**
     * Activate a tenant and dispatch provisioning.
     */
    public function activate(int $tenant)
    {
        $this->manager->activate($this->tenants->findOrFail($tenant));

        return back()->with('success', trans('saas::app.super-admin.tenants.activation-queued'));
    }

    /**
     * Suspend a tenant and its subscription.
     */
    public function suspend(Request $request, int $tenant)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->manager->suspend($this->tenants->findOrFail($tenant), $data['reason']);

        return back()->with('success', trans('saas::app.super-admin.tenants.suspended'));
    }

    /**
     * Lift a suspension.
     */
    public function reactivate(int $tenant)
    {
        $this->manager->reactivate($this->tenants->findOrFail($tenant));

        return back()->with('success', trans('saas::app.super-admin.tenants.reactivated'));
    }

    /**
     * Cancel a tenant, its subscription and its domains.
     */
    public function cancel(Request $request, int $tenant)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->manager->cancel($this->tenants->findOrFail($tenant), $data['reason']);

        return back()->with('success', trans('saas::app.super-admin.tenants.cancelled'));
    }

    /**
     * Soft delete a cancelled tenant.
     */
    public function destroy(Request $request, int $tenant)
    {
        $data = $request->validate([
            'confirmation' => ['required', 'string'],
        ]);

        $this->manager->delete($this->tenants->findOrFail($tenant), $data['confirmation']);

        return redirect()
            ->route('saas.super-admin.tenants.index')
            ->with('success', trans('saas::app.super-admin.tenants.deleted'));
    }

    /**
     * Queue a tenant data export.
     */
    public function export(int $tenant)
    {
        $tenant = $this->tenants->findOrFail($tenant);

        $this->manager->export($tenant);

        $this->logger->forTenant($tenant, AuditAction::TenantExported, [
            'description' => 'Tenant export queued from the platform panel.',
        ]);

        return back()->with('success', trans('saas::app.super-admin.tenants.export-queued'));
    }

    /**
     * Re-run provisioning for a tenant.
     */
    public function provision(int $tenant)
    {
        $tenant = $this->tenants->findOrFail($tenant);

        if ($tenant->isServing()) {
            return back()->with('error', trans('saas::app.super-admin.tenants.already-active'));
        }

        $this->provisioning->retry($tenant);

        return back()->with('success', trans('saas::app.super-admin.tenants.provisioning-queued'));
    }
}
