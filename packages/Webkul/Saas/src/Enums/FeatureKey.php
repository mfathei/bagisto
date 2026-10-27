<?php

namespace Webkul\Saas\Enums;

enum FeatureKey: string
{
    case MaxProducts = 'max_products';
    case MaxStaff = 'max_staff';
    case MonthlyOrders = 'monthly_orders';
    case StorageMb = 'storage_mb';
    case CustomDomain = 'custom_domain';
    case AdvancedAnalytics = 'advanced_analytics';
    case ApiAccess = 'api_access';
    case PremiumSupport = 'premium_support';

    /**
     * Human readable label for the feature.
     */
    public function label(): string
    {
        return (string) config('saas.entitlements.features.'.$this->value, $this->value);
    }

    /**
     * Whether the feature is a numeric quota rather than a boolean entitlement.
     */
    public function isQuota(): bool
    {
        return in_array($this, [
            self::MaxProducts,
            self::MaxStaff,
            self::MonthlyOrders,
            self::StorageMb,
        ], true);
    }

    /**
     * The usage metric backing this feature, when it is a quota.
     */
    public function metric(): ?string
    {
        return match ($this) {
            self::MaxProducts => 'products',
            self::MaxStaff => 'staff',
            self::MonthlyOrders => 'orders',
            self::StorageMb => 'storage_mb',
            default => null,
        };
    }
}
