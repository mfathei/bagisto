<?php

namespace Webkul\Saas\Enums;

enum ContactInquiryStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';

    /**
     * Human readable label for the status.
     */
    public function label(): string
    {
        return (string) trans('saas::marketing.admin.inquiries.statuses.'.$this->value);
    }

    /**
     * Whether the inquiry still needs attention.
     */
    public function isOpen(): bool
    {
        return $this !== self::Resolved;
    }
}
