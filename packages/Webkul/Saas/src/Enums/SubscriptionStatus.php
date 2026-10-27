<?php

namespace Webkul\Saas\Enums;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    /**
     * Human readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Trialing => 'Trialing',
            self::Active => 'Active',
            self::PastDue => 'Past due',
            self::Suspended => 'Suspended',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }

    /**
     * Whether the subscription still grants service.
     */
    public function grantsAccess(): bool
    {
        return in_array($this, [self::Trialing, self::Active, self::PastDue], true);
    }

    /**
     * Whether the subscription is counted as active paid revenue.
     */
    public function isPaidAndCurrent(): bool
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
            self::Trialing => in_array($target, [self::Active, self::PastDue, self::Cancelled, self::Expired], true),
            self::Active => in_array($target, [self::PastDue, self::Suspended, self::Cancelled, self::Expired], true),
            self::PastDue => in_array($target, [self::Active, self::Suspended, self::Cancelled, self::Expired], true),
            self::Suspended => in_array($target, [self::Active, self::Cancelled, self::Expired], true),
            self::Cancelled, self::Expired => in_array($target, [self::Active], true),
        };
    }
}
