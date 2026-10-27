<?php

namespace Webkul\Saas\Enums;

enum SslStatus: string
{
    case Pending = 'pending';
    case Issued = 'issued';
    case Failed = 'failed';

    /**
     * Human readable label for the SSL status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Issued => 'Issued',
            self::Failed => 'Failed',
        };
    }
}
