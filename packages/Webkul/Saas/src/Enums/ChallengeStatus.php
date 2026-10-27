<?php

namespace Webkul\Saas\Enums;

enum ChallengeStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Failed = 'failed';

    /**
     * Human readable label for the challenge status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Verified => 'Verified',
            self::Failed => 'Failed',
        };
    }
}
