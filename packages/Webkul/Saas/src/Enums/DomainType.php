<?php

namespace Webkul\Saas\Enums;

enum DomainType: string
{
    case PlatformSubdomain = 'platform_subdomain';
    case Custom = 'custom';

    /**
     * Human readable label for the domain type.
     */
    public function label(): string
    {
        return match ($this) {
            self::PlatformSubdomain => 'Platform subdomain',
            self::Custom => 'Custom domain',
        };
    }
}
