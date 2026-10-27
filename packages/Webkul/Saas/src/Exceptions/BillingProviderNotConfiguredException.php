<?php

namespace Webkul\Saas\Exceptions;

use RuntimeException;

class BillingProviderNotConfiguredException extends RuntimeException
{
    /**
     * Create a new provider failure.
     */
    public function __construct(string $message = 'No payment provider is configured. Record payments manually or configure an adapter.')
    {
        parent::__construct($message);
    }
}
