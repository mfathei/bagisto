<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\AuditLogRepository;
use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Repositories\ProvisioningOperationRepository;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Repositories\UsageMetricRepository;
use Webkul\Saas\Services\AuditLogger;
use Webkul\Saas\Services\ProvisioningService;
use Webkul\Saas\Services\TenantManager;

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
        protected AuditLogger $logger
    ) {}

    /**
     * List tenants with filters.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'plan_id', 'sort', 'direction']);

        return view('saas::super-admin.tenants.index', [
            'tenants' => $this->tenants->paginateFiltered($filters),
            'plans' => $this->plans->orderBy('sort_order')->get(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the tenant creation form.
     */
    public function create()
    {
        return view('saas::super-admin.tenants.create', [
            'plans' => $this->plans->orderBy('sort_order')->get(),
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
        ]);
    }

    /**
     * Update the descriptive details of a tenant.
     */
    public function update(Request $request, int $tenant)
    {
        $tenant = $this->tenants->findOrFail($tenant);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:40'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:12'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $this->manager->update($tenant, $data);

        return back()->with('success', trans('saas::app.super-admin.tenants.updated'));
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
