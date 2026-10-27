<?php

namespace Webkul\Saas\Services\Billing;

use Webkul\Saas\Contracts\BillingProvider;
use Webkul\Saas\Contracts\BillingRecord;
use Webkul\Saas\Contracts\PaymentTransaction;
use Webkul\Saas\Exceptions\BillingProviderNotConfiguredException;

class UnconfiguredBillingProvider implements BillingProvider
{
    /**
     * The provider identifier from configuration.
     */
    public function name(): string
    {
        return (string) config('saas.billing.provider', 'unconfigured');
    }

    /**
     * No external provider is wired for this identifier.
     */
    public function isConfigured(): bool
    {
        return false;
    }

    /**
     * Fail explicitly instead of pretending an invoice exists externally.
     *
     * @return array{provider_invoice_id: string|null}
     */
    public function createInvoice(BillingRecord $invoice): array
    {
        throw new BillingProviderNotConfiguredException($this->message());
    }

    /**
     * Fail explicitly instead of recording a payment that did not happen.
     *
     * @param  array<string, mixed>  $payment
     */
    public function charge(BillingRecord $invoice, array $payment): BillingResult
    {
        throw new BillingProviderNotConfiguredException($this->message());
    }

    /**
     * Fail explicitly, as no refund can be issued without a provider.
     */
    public function refund(PaymentTransaction $transaction, ?float $amount = null): BillingResult
    {
        throw new BillingProviderNotConfiguredException($this->message());
    }

    /**
     * The operator-facing explanation for the missing adapter.
     */
    protected function message(): string
    {
        return trans('saas::app.billing.provider-not-configured', ['provider' => $this->name()]);
    }
}
