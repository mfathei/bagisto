<?php

namespace Webkul\Saas\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Webkul\Saas\Contracts\TenantDomain;
use Webkul\Saas\Enums\DomainStatus;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Mail\DomainVerifiedMail;
use Webkul\Saas\Repositories\TenantDomainRepository;
use Webkul\Saas\Services\AuditLogger;
use Webkul\Saas\Services\DomainService;

class DomainController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TenantDomainRepository $domains,
        protected DomainService $service,
        protected AuditLogger $audit
    ) {}

    /**
     * List the hostnames attached to the tenant.
     */
    public function index()
    {
        $this->authorizeAdministrative();

        $tenant = $this->tenant();

        return view('saas::tenant.domains', [
            'tenant' => $tenant,
            'domains' => $this->domains
                ->with(['challenges' => fn ($query) => $query->orderByDesc('id')])
                ->where('tenant_id', $tenant->getKey())
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->get(),
            'suffix' => $this->service->suffix(),
        ]);
    }

    /**
     * Attach a custom hostname pending DNS verification.
     */
    public function store(Request $request)
    {
        $this->authorizeAdministrative();

        $data = $request->validate([
            'hostname' => ['required', 'string', 'max:191'],
        ]);

        $this->service->addCustomDomain($this->tenant(), $data['hostname']);

        return back()->with('success', trans('saas::app.tenant.domains.added'));
    }

    /**
     * Check DNS and verify ownership of a pending domain.
     */
    public function verify(int $domain)
    {
        $this->authorizeAdministrative();

        $record = $this->owned($domain);

        $result = $this->service->verify($record);

        if ($result['verified']) {
            $this->sendVerifiedMail($record);
        }

        return back()->with(
            $result['verified'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Make a verified domain the primary hostname.
     */
    public function primary(int $domain)
    {
        $this->authorizeAdministrative();

        $this->service->setPrimary($this->tenant(), $this->owned($domain));

        return back()->with('success', trans('saas::app.tenant.domains.primary-updated'));
    }

    /**
     * Detach a domain, refusing to remove the last servable hostname.
     */
    public function destroy(int $domain)
    {
        $this->authorizeAdministrative();

        $tenant = $this->tenant();
        $record = $this->owned($domain);

        if ($record->status === DomainStatus::Verified) {
            $otherVerified = $this->domains
                ->where('tenant_id', $tenant->getKey())
                ->where('status', DomainStatus::Verified->value)
                ->whereKeyNot($record->getKey())
                ->exists();

            if (! $otherVerified) {
                throw ValidationException::withMessages([
                    'domain' => trans('saas::app.errors.last-domain'),
                ]);
            }
        }

        $this->service->remove($tenant, $record);

        return back()->with('success', trans('saas::app.tenant.domains.removed'));
    }

    /**
     * Resolve a domain belonging to the current tenant, rejecting cross-tenant ids.
     */
    protected function owned(int $domain): TenantDomain
    {
        $record = $this->domains->findOrFail($domain);
        $tenant = $this->tenant();

        abort_unless((int) $record->tenant_id === (int) $tenant->getKey(), 404);

        return $record;
    }

    /**
     * Send the domain verification notice, logging rather than failing the request.
     */
    protected function sendVerifiedMail(TenantDomain $domain): void
    {
        $domain->loadMissing('tenant');

        $recipient = $domain->tenant?->email;

        if (! $recipient) {
            return;
        }

        try {
            Mail::to($recipient)->send(new DomainVerifiedMail($domain->tenant, $domain->hostname));
        } catch (\Throwable $exception) {
            Log::warning('SaaS domain verification mail could not be sent.', [
                'hostname' => $domain->hostname,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
