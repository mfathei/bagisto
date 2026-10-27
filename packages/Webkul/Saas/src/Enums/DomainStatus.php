<?php

namespace Webkul\Saas\Enums;

enum DomainStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Failed = 'failed';
    case Disabled = 'disabled';

    /**
     * Human readable label for the domain status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending verification',
            self::Verified => 'Verified',
            self::Failed => 'Verification failed',
            self::Disabled => 'Disabled',
        };
    }

    /**
     * Whether the domain may serve traffic.
     */
    public function isServable(): bool
    {
        return $this === self::Verified;
    }
}
