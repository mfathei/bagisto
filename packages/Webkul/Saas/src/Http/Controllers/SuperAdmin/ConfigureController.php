<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\AuditLogRepository;
use Webkul\Saas\Repositories\ProvisioningOperationRepository;
use Webkul\Saas\Repositories\TenantDomainRepository;
use Webkul\Saas\Services\PlatformSettings;

class ConfigureController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TenantDomainRepository $domains,
        protected ProvisioningOperationRepository $operations,
        protected AuditLogRepository $audit,
        protected PlatformSettings $settings
    ) {}

    /**
     * Show the platform configuration hub.
     */
    public function index()
    {
        return view('saas::super-admin.configure.index', [
            'domainCount' => $this->domains->getModel()->newQuery()->count(),
            'pendingDomains' => $this->domains->pendingCustomDomains()->count(),
            'failedOperations' => $this->operations->failed()->count(),
            'auditCount' => $this->audit->getModel()->newQuery()->count(),
            'platformName' => $this->settings->platformName(),
            'supportEmail' => $this->settings->supportEmail(),
            'registrationEnabled' => $this->settings->registrationEnabled(),
            'requiresApproval' => $this->settings->requiresApproval(),
            'defaultTrialDays' => $this->settings->defaultTrialDays(),
        ]);
    }
}
