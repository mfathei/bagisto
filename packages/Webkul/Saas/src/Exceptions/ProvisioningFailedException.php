<?php

namespace Webkul\Saas\Exceptions;

use RuntimeException;

class ProvisioningFailedException extends RuntimeException
{
    /**
     * Create a new provisioning failure.
     */
    public function __construct(
        public readonly string $step,
        string $message,
        public readonly ?\Throwable $previousError = null
    ) {
        parent::__construct($message, 0, $previousError);
    }
}
