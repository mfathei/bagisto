<?php

namespace Webkul\Saas\Exceptions;

use RuntimeException;

class CrossTenantAccessException extends RuntimeException
{
    /**
     * Create a new cross-tenant access failure.
     */
    public function __construct(string $message = 'This resource does not belong to the active tenant.')
    {
        parent::__construct($message);
    }
}
