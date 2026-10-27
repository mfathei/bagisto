<?php

namespace Webkul\Saas\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Webkul\Saas\Contracts\BillingProvider;
use Webkul\Saas\Contracts\BillingRecord;
use Webkul\Saas\Contracts\PaymentTransaction;
use Webkul\Saas\Contracts\Subscription;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Enums\BillingStatus;
use Webkul\Saas\Enums\PaymentStatus;
use Webkul\Saas\Enums\SubscriptionStatus;
use Webkul\Saas\Repositories\BillingRecordRepository;
use Webkul\Saas\Repositories\PaymentTransactionRepository;
use Webkul\Saas\Repositories\SubscriptionRepository;

class BillingService
{
    /**
     * Create a new billing service.
     */
    public function __construct(
        protected BillingRecordRepository $invoices,
        protected PaymentTransactionRepository $transactions,
        protected SubscriptionRepository $subscriptions,
        protected BillingProvider $provider,
        protected SubscriptionService $subscriptionService,
        protected AuditLogger $audit
    ) {}

    /**
     * Invoice the current billing period of a paid subscription.
     */
    public function invoiceSubscription(Subscription $subscription, ?Carbon $periodStart = null, ?Carbon $periodEnd = null): ?BillingRecord
    {
        $plan = $subscription->plan;

        if (! $plan) {
            return null;
        }

        $total = round((float) $plan->price, 2);

        if ($total <= 0) {
            return null;
        }

        $periodStart = $periodStart ?? $subscription->current_period_start ?? now();
        $periodEnd = $periodEnd ?? $subscription->current_period_end ?? $periodStart->copy()->addMonth();

        $existing = $this->invoices->scopeQuery(function ($query) use ($subscription, $periodStart) {
            return $query
                ->where('subscription_id', $subscription->getKey())
                ->where('period_start', $periodStart)
                ->whereIn('status', [
                    BillingStatus::Open->value,
                    BillingStatus::PastDue->value,
                    BillingStatus::Paid->value,
                ]);
        })->first();

        if ($existing) {
            return $existing;
        }

        $invoice = $this->invoices->create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->getKey(),
            'number' => $this->invoices->nextNumber(),
            'status' => BillingStatus::Open->value,
            'currency' => $plan->currency ?: config('saas.billing.currency'),
            'subtotal' => $total,
            'tax' => 0,
            'total' => $total,
            'amount_paid' => 0,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'due_at' => now()->addDays((int) config('saas.billing.invoice_due_days', 7)),
            'provider' => $this->provider->name(),
            'metadata' => ['plan' => $plan->slug],
        ]);

        $reference = $this->provider->createInvoice($invoice);

        if (! empty($reference['provider_invoice_id'])) {
            $invoice->forceFill(['provider_invoice_id' => $reference['provider_invoice_id']])->save();
        }

        $this->audit->forTenant($subscription->tenant, AuditAction::InvoiceCreated, [
            'subject' => $invoice,
            'description' => 'Subscription invoice created.',
            'metadata' => [
                'number' => $invoice->number,
                'total' => $invoice->total,
                'currency' => $invoice->currency,
            ],
        ]);

        return $invoice;
    }

    /**
     * Record a payment against an invoice, verifying the amount and currency.
     *
     * @param  array<string, mixed>  $payment
     */
    public function recordPayment(BillingRecord $invoice, array $payment): PaymentTransaction
    {
        if (in_array($invoice->status, [BillingStatus::Paid, BillingStatus::Void, BillingStatus::Refunded], true)) {
            throw ValidationException::withMessages([
                'payment' => trans('saas::app.billing.invoice-not-payable'),
            ]);
        }

        $currency = strtoupper((string) ($payment['currency'] ?? $invoice->currency));
        $amount = round((float) ($payment['amount'] ?? $invoice->balance()), 2);
        $balance = round($invoice->balance(), 2);

        if ($currency !== strtoupper((string) $invoice->currency)) {
            throw ValidationException::withMessages([
                'payment' => trans('saas::app.billing.currency-mismatch'),
            ]);
        }

        if ($amount <= 0 || $amount > $balance + 0.005) {
            throw ValidationException::withMessages([
                'payment' => trans('saas::app.billing.amount-invalid', ['balance' => $balance]),
            ]);
        }

        $reference = trim((string) ($payment['reference'] ?? ''));

        if ($reference !== '') {
            $existing = $this->transactions->findSuccessfulByProviderReference($this->provider->name(), $reference);

            if ($existing) {
                if (round((float) $existing->amount, 2) !== $amount) {
                    throw ValidationException::withMessages([
                        'payment' => trans('saas::app.billing.reference-conflict'),
                    ]);
                }

                return $existing;
            }
        }

        $result = $this->provider->charge($invoice, $payment);

        if (! $result->successful) {
            $this->transactions->create([
                'billing_record_id' => $invoice->getKey(),
                'provider' => $this->provider->name(),
                'provider_transaction_id' => $reference ?: null,
                'amount' => $amount,
                'currency' => $currency,
                'status' => PaymentStatus::Failed->value,
                'metadata' => array_merge($result->metadata, ['message' => $result->message]),
                'processed_at' => now(),
            ]);

            $this->audit->forTenant($invoice->tenant, AuditAction::PaymentFailed, [
                'subject' => $invoice,
                'description' => 'Payment attempt failed.',
                'metadata' => ['amount' => $amount, 'currency' => $currency],
            ]);

            throw ValidationException::withMessages([
                'payment' => $result->message ?? trans('saas::app.billing.payment-failed'),
            ]);
        }

        $transaction = $this->transactions->create([
            'billing_record_id' => $invoice->getKey(),
            'provider' => $this->provider->name(),
            'provider_transaction_id' => $result->reference ?: $reference ?: null,
            'amount' => $amount,
            'currency' => $currency,
            'status' => PaymentStatus::Succeeded->value,
            'metadata' => $result->metadata,
            'processed_at' => now(),
        ]);

        $paid = round((float) $invoice->amount_paid + $amount, 2);

        $invoice->forceFill([
            'amount_paid' => $paid,
            'status' => $paid + 0.005 >= (float) $invoice->total ? BillingStatus::Paid->value : BillingStatus::Open->value,
            'paid_at' => $paid + 0.005 >= (float) $invoice->total ? now() : null,
        ])->save();

        $this->restoreServiceAfterPayment($invoice);

        $this->audit->forTenant($invoice->tenant, AuditAction::PaymentRecorded, [
            'subject' => $invoice,
            'description' => 'Payment recorded against invoice '.$invoice->number.'.',
            'metadata' => [
                'amount' => $amount,
                'currency' => $currency,
                'reference' => $result->reference,
                'paid_in_full' => $paid + 0.005 >= (float) $invoice->total,
            ],
        ]);

        return $transaction;
    }

    /**
     * Flag overdue invoices and move their subscriptions past due.
     */
    public function markOverdueInvoices(): int
    {
        $count = 0;

        $overdue = $this->invoices->scopeQuery(function ($query) {
            return $query
                ->with('subscription')
                ->where('status', BillingStatus::Open->value)
                ->whereNotNull('due_at')
                ->where('due_at', '<', now());
        })->get();

        foreach ($overdue as $invoice) {
            $invoice->forceFill(['status' => BillingStatus::PastDue->value])->save();

            $subscription = $invoice->subscription;

            if ($subscription && $subscription->status === SubscriptionStatus::Active) {
                $this->subscriptionService->markPastDue($subscription);
            }

            $count++;
        }

        return $count;
    }

    /**
     * Create invoices for subscription periods that have just elapsed.
     */
    public function invoiceDuePeriods(): int
    {
        $count = 0;

        $due = $this->subscriptions->scopeQuery(function ($query) {
            return $query
                ->with(['plan', 'tenant'])
                ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::PastDue->value])
                ->whereNotNull('current_period_end')
                ->where('current_period_end', '<=', now());
        })->get();

        foreach ($due as $subscription) {
            DB::transaction(function () use ($subscription, &$count) {
                if ($subscription->cancel_at_period_end) {
                    $this->subscriptionService->cancel($subscription->tenant, false, 'Cancelled at period end.');

                    return;
                }

                $periodStart = $subscription->current_period_start ?? now();
                $periodEnd = $subscription->current_period_end ?? now();

                $invoice = $this->invoiceSubscription($subscription, $periodStart, $periodEnd);

                if (! $invoice) {
                    return;
                }

                if ($invoice->wasRecentlyCreated) {
                    $count++;
                }

                $this->subscriptionService->advancePeriod($subscription, $periodEnd);
            });
        }

        return $count;
    }

    /**
     * Monthly recurring revenue estimated from active subscriptions.
     */
    public function monthlyRecurringRevenue(): float
    {
        $active = $this->subscriptions->scopeQuery(function ($query) {
            return $query
                ->with('plan')
                ->where('status', SubscriptionStatus::Active->value);
        })->get();

        return round($active->sum(fn ($subscription) => (float) ($subscription->plan?->monthlyPrice() ?? 0)), 2);
    }

    /**
     * Payments actually captured in a period.
     */
    public function collectedRevenue(?Carbon $from = null, ?Carbon $to = null): float
    {
        $payments = $this->transactions->scopeQuery(function ($query) use ($from, $to) {
            return $query
                ->where('status', PaymentStatus::Succeeded->value)
                ->when($from, fn ($query) => $query->where('processed_at', '>=', $from))
                ->when($to, fn ($query) => $query->where('processed_at', '<=', $to));
        })->get();

        return round((float) $payments->sum('amount'), 2);
    }

    /**
     * Invoices belonging to a tenant.
     */
    public function forTenant(Tenant $tenant)
    {
        return $this->invoices->forTenant($tenant->getKey());
    }

    /**
     * The configured payment provider adapter.
     */
    public function provider(): BillingProvider
    {
        return $this->provider;
    }

    /**
     * Resume service once the invoice covering the current period is paid.
     */
    protected function restoreServiceAfterPayment(BillingRecord $invoice): void
    {
        if ($invoice->status !== BillingStatus::Paid) {
            return;
        }

        $subscription = $invoice->subscription;

        if (! $subscription) {
            return;
        }

        if (in_array($subscription->status, [SubscriptionStatus::PastDue, SubscriptionStatus::Suspended], true)) {
            $this->subscriptionService->resume($subscription->tenant);
        }
    }
}
