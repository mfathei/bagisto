<?php

namespace Webkul\Saas\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Saas\Models\AuditLog;
use Webkul\Saas\Models\BillingRecord;
use Webkul\Saas\Models\ContactInquiry;
use Webkul\Saas\Models\DomainChallenge;
use Webkul\Saas\Models\PaymentTransaction;
use Webkul\Saas\Models\Plan;
use Webkul\Saas\Models\PlatformSetting;
use Webkul\Saas\Models\ProvisioningOperation;
use Webkul\Saas\Models\Subscription;
use Webkul\Saas\Models\SuperAdmin;
use Webkul\Saas\Models\Tenant;
use Webkul\Saas\Models\TenantDomain;
use Webkul\Saas\Models\TenantEntitlement;
use Webkul\Saas\Models\TenantInvitation;
use Webkul\Saas\Models\TenantUser;
use Webkul\Saas\Models\UsageMetric;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        AuditLog::class,
        BillingRecord::class,
        ContactInquiry::class,
        DomainChallenge::class,
        PaymentTransaction::class,
        Plan::class,
        PlatformSetting::class,
        ProvisioningOperation::class,
        Subscription::class,
        SuperAdmin::class,
        Tenant::class,
        TenantDomain::class,
        TenantEntitlement::class,
        TenantInvitation::class,
        TenantUser::class,
        UsageMetric::class,
    ];
}
