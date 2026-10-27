<?php

namespace Webkul\Saas\Services\Billing;

use Webkul\Saas\Contracts\BillingProvider;
use Webkul\Saas\Contracts\BillingRecord;
use Webkul\Saas\Contracts\PaymentTransaction;
use Webkul\Saas\Exceptions\BillingProviderNotConfiguredException;
use Webkul\Saas\Exceptions\CrossTenantAccessException;

class ManualBillingProvider implements BillingProvider
{
    /**
     * The provider identifier.
     */
    public function name(): string
    {
        return 'manual';
    }

    /**
     * Manual billing is available without external credentials.
     */
    public function isConfigured(): bool
    {
        return true;
    }

    /**
     * Manual invoices do not exist outside the platform.
     *
     * @return array{provider_invoice_id: string|null}
     */
    public function createInvoice(BillingRecord $invoice): array
    {
        return ['provider_invoice_id' => null];
    }

    /**
     * Record an offline payment attested by a Super Admin.
     *
     * @param  array<string, mixed>  $payment
     */
    public function charge(BillingRecord $invoice, array $payment): BillingResult
    {
        if (($payment['confirmed'] ?? false) !== true) {
            return BillingResult::failure(trans('saas::app.billing.confirmation-required'));
        }

        $reference = trim((string) ($payment['reference'] ?? ''));

        if ($reference === '') {
            return BillingResult::failure(trans('saas::app.billing.reference-required'));
        }

        if (mb_strlen($reference) > 191) {
            return BillingResult::failure(trans('saas::app.billing.reference-invalid'));
        }

        return BillingResult::success($reference, trans('saas::app.billing.recorded'), [
            'method' => (string) ($payment['method'] ?? 'bank_transfer'),
            'recorded_by' => auth('super_admin')->id(),
        ]);
    }

    /**
     * Manual refunds are handled outside the platform by policy.
     */
    public function refund(PaymentTransaction $transaction, ?float $amount = null): BillingResult
    {
        throw new BillingProviderNotConfiguredException(
            trans('saas::app.billing.refund-not-supported')
        );
    }

    /**
     * Guard against cross-tenant billing operations.
     */
    public function assertSameTenant(BillingRecord $invoice, int $tenantId): void
    {
        if ((int) $invoice->tenant_id !== $tenantId) {
            throw new CrossTenantAccessException;
        }
    }
}
