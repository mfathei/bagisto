<?php

namespace Webkul\Saas\Exceptions;

use RuntimeException;

class TenantResolutionException extends RuntimeException
{
    /**
     * Create a new resolution failure.
     */
    public function __construct(
        public readonly string $host,
        public readonly string $reason,
        string $message = 'The requested store is not available.'
    ) {
        parent::__construct($message);
    }

    /**
     * Render the failure without disclosing tenant details.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 404);
        }

        abort($this->reason === 'suspended' ? 503 : 404, $this->getMessage());
    }
}
