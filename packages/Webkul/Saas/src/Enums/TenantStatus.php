<?php

namespace Webkul\Saas\Enums;

enum TenantStatus: string
{
    case Pending = 'pending';
    case Provisioning = 'provisioning';
    case Active = 'active';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';

    /**
     * Human readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Provisioning => 'Provisioning',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Whether the tenant may be resolved as a live storefront.
     */
    public function isServing(): bool
    {
        return $this === self::Active;
    }

    /**
     * Whether a transition to the given status is allowed.
     */
    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return false;
        }

        return match ($this) {
            self::Pending => in_array($target, [self::Provisioning, self::Cancelled], true),
            self::Provisioning => in_array($target, [self::Active, self::Pending, self::Cancelled], true),
            self::Active => in_array($target, [self::Suspended, self::Cancelled], true),
            self::Suspended => in_array($target, [self::Active, self::Cancelled], true),
            self::Cancelled => false,
        };
    }

    /**
     * Statuses that count as retained, billable or newly registered tenants.
     *
     * @return string[]
     */
    public static function registered(): array
    {
        return array_column(self::cases(), 'value');
    }
}
