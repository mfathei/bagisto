<?php

namespace Webkul\Saas\Services\Billing;

class BillingResult
{
    /**
     * Create a new provider result.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly bool $successful,
        public readonly ?string $reference = null,
        public readonly ?string $providerInvoiceId = null,
        public readonly ?string $message = null,
        public readonly array $metadata = []
    ) {}

    /**
     * Build a successful result.
     *
     * @param  array<string, mixed>  $metadata
     */
    public static function success(?string $reference = null, ?string $message = null, array $metadata = []): self
    {
        return new self(true, $reference, null, $message, $metadata);
    }

    /**
     * Build a failed result.
     */
    public static function failure(string $message, array $metadata = []): self
    {
        return new self(false, null, null, $message, $metadata);
    }
}
