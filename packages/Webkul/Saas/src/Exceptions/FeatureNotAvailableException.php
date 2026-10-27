<?php

namespace Webkul\Saas\Exceptions;

use RuntimeException;

class FeatureNotAvailableException extends RuntimeException
{
    /**
     * Create a new feature entitlement failure.
     */
    public function __construct(public readonly string $feature, ?string $message = null)
    {
        parent::__construct($message ?: trans('saas::app.errors.feature-unavailable', ['feature' => $feature]));
    }

    /**
     * Render the failure for HTTP responses.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'feature' => $this->feature,
            ], 403);
        }

        abort(403, $this->getMessage());
    }
}
