<?php

namespace Webkul\Saas\Enums;

enum AuditAction: string
{
    case TenantCreated = 'tenant.created';
    case TenantUpdated = 'tenant.updated';
    case TenantApproved = 'tenant.approved';
    case TenantActivated = 'tenant.activated';
    case TenantSuspended = 'tenant.suspended';
    case TenantReactivated = 'tenant.reactivated';
    case TenantCancelled = 'tenant.cancelled';
    case TenantDeleted = 'tenant.deleted';
    case TenantExported = 'tenant.exported';
    case DomainAdded = 'domain.added';
    case DomainVerified = 'domain.verified';
    case DomainRemoved = 'domain.removed';
    case DomainPrimaryChanged = 'domain.primary_changed';
    case SslIssued = 'domain.ssl_issued';
    case PlanCreated = 'plan.created';
    case PlanUpdated = 'plan.updated';
    case PlanDeleted = 'plan.deleted';
    case SubscriptionCreated = 'subscription.created';
    case SubscriptionPlanChanged = 'subscription.plan_changed';
    case SubscriptionTrialExtended = 'subscription.trial_extended';
    case SubscriptionCancelled = 'subscription.cancelled';
    case SubscriptionReactivated = 'subscription.reactivated';
    case SubscriptionSuspended = 'subscription.suspended';
    case SubscriptionExpired = 'subscription.expired';
    case SubscriptionPastDue = 'subscription.past_due';
    case InvoiceCreated = 'billing.invoice_created';
    case PaymentRecorded = 'billing.payment_recorded';
    case PaymentFailed = 'billing.payment_failed';
    case ProvisioningStarted = 'provisioning.started';
    case ProvisioningCompleted = 'provisioning.completed';
    case ProvisioningFailed = 'provisioning.failed';
    case ProvisioningRetried = 'provisioning.retried';
    case UserInvited = 'user.invited';
    case UserJoined = 'user.joined';
    case UserRoleChanged = 'user.role_changed';
    case UserSuspended = 'user.suspended';
    case UserRemoved = 'user.removed';
    case SuperAdminCreated = 'super_admin.created';
    case SuperAdminLogin = 'super_admin.login';
    case SettingUpdated = 'setting.updated';
    case TenantSettingsUpdated = 'tenant.settings_updated';
    case InquiryUpdated = 'inquiry.updated';
    case LegacyDataAssigned = 'bootstrap.legacy_data_assigned';

    /**
     * Human readable label for the audit action.
     */
    public function label(): string
    {
        return ucfirst(str_replace(['.', '_'], [' ', ' '], $this->value));
    }
}
