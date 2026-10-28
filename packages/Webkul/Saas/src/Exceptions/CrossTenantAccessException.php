<?php

namespace Webkul\Saas\Exceptions;

use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class CrossTenantAccessException extends RuntimeException
{
    /**
     * Create a new cross-tenant access failure.
     */
    public function __construct(string $message = 'This resource does not belong to the active tenant.')
    {
        parent::__construct($message);
    }

    /**
     * Render the failure as a denied request instead of a server error.
     */
    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 403);
        }

        return response($this->getMessage(), 403);
    }
}
