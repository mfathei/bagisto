<?php

namespace Webkul\Saas\Exceptions;

use Illuminate\Http\Request;
use RuntimeException;

class QuotaExceededException extends RuntimeException
{
    /**
     * Create a new quota failure.
     */
    public function __construct(
        public readonly string $feature,
        public readonly ?int $limit,
        public readonly float $usage,
        ?string $message = null
    ) {
        parent::__construct($message ?: trans('saas::app.errors.quota-exceeded', [
            'feature' => $feature,
            'limit' => $limit ?? '∞',
        ]));
    }

    /**
     * Render the failure as a validation error.
     */
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'errors' => ['quota' => [$this->getMessage()]],
                'feature' => $this->feature,
                'limit' => $this->limit,
                'usage' => $this->usage,
            ], 422);
        }

        return back()->withInput()->withErrors(['quota' => $this->getMessage()]);
    }
}
