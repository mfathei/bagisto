<?php

namespace Webkul\Saas\Contracts;

use Webkul\Saas\Services\Billing\BillingResult;

interface BillingProvider
{
    /**
     * The provider identifier stored on billing records.
     */
    public function name(): string;

    /**
     * Whether the provider has everything it needs to take payments.
     */
    public function isConfigured(): bool;

    /**
     * Create the provider-side representation of an invoice.
     *
     * @return array{provider_invoice_id: string|null}
     */
    public function createInvoice(BillingRecord $invoice): array;

    /**
     * Charge an invoice using the supplied, already validated payment details.
     *
     * @param  array<string, mixed>  $payment
     */
    public function charge(BillingRecord $invoice, array $payment): BillingResult;

    /**
     * Refund a captured payment.
     */
    public function refund(PaymentTransaction $transaction, ?float $amount = null): BillingResult;
}
