<?php

namespace Webkul\Saas\Enums;

enum MembershipRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case StoreManager = 'store_manager';
    case CatalogManager = 'catalog_manager';
    case OrderManager = 'order_manager';
    case Support = 'support';

    /**
     * Human readable label for the membership role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Administrator',
            self::StoreManager => 'Store manager',
            self::CatalogManager => 'Catalog manager',
            self::OrderManager => 'Order manager',
            self::Support => 'Support',
        };
    }

    /**
     * Whether the role may manage the tenant team and billing.
     */
    public function isAdministrative(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    /**
     * Roles that may be assigned through invitations, never owner.
     *
     * @return string[]
     */
    public static function assignable(): array
    {
        return array_values(array_map(
            fn (self $role) => $role->value,
            array_filter(self::cases(), fn (self $role) => $role !== self::Owner)
        ));
    }
}
