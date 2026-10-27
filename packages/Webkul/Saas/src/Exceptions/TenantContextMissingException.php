<?php

namespace Webkul\Saas\Exceptions;

use RuntimeException;

class TenantContextMissingException extends RuntimeException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = 'A tenant context is required for this operation.')
    {
        parent::__construct($message);
    }

    /**
     * Render the exception for HTTP responses.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 403);
        }

        abort(403, $this->getMessage());
    }
}
