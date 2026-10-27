<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Mail\DomainVerifiedMail;
use Webkul\Saas\Repositories\TenantDomainRepository;
use Webkul\Saas\Services\DomainService;

class DomainController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TenantDomainRepository $domains,
        protected DomainService $service
    ) {}

    /**
     * List every domain with its tenant.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'type', 'search']);

        $domains = $this->domains->scopeQuery(function ($query) use ($filters) {
            return $query
                ->with('tenant')
                ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
                ->when(! empty($filters['type']), fn ($query) => $query->where('type', $filters['type']))
                ->when(! empty($filters['search']), fn ($query) => $query->where('hostname', 'like', '%'.$filters['search'].'%'))
                ->orderByDesc('created_at');
        })->paginate(20);

        return view('saas::super-admin.domains.index', [
            'domains' => $domains,
            'filters' => $filters,
        ]);
    }

    /**
     * Re-check a domain challenge.
     */
    public function verify(int $domain)
    {
        $domain = $this->domains->findOrFail($domain);

        $result = $this->service->verify($domain);

        if ($result['verified']) {
            try {
                Mail::to($domain->tenant->email)->send(new DomainVerifiedMail($domain->tenant, $domain->hostname));
            } catch (\Throwable $exception) {
                Log::warning('SaaS domain verified mail could not be sent.', [
                    'domain' => $domain->hostname,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        return back()->with(
            $result['verified'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Make a verified domain the tenant's primary hostname.
     */
    public function primary(Request $request, int $domain)
    {
        $domain = $this->domains->findOrFail($domain);

        $this->service->setPrimary($domain->tenant, $domain);

        return back()->with('success', trans('saas::app.super-admin.domains.primary-updated'));
    }
}
