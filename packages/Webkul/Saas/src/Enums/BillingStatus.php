<?php

namespace Webkul\Saas\Enums;

enum BillingStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case PastDue = 'past_due';
    case Paid = 'paid';
    case Void = 'void';
    case Refunded = 'refunded';

    /**
     * Human readable label for the billing status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Open => 'Open',
            self::PastDue => 'Past due',
            self::Paid => 'Paid',
            self::Void => 'Void',
            self::Refunded => 'Refunded',
        };
    }
}
