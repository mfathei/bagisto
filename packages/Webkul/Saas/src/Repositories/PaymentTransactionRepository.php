<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\PaymentTransaction;

class PaymentTransactionRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PaymentTransaction::class;
    }

    /**
     * Find a provider transaction for idempotent webhook handling.
     */
    public function findByProviderReference(string $provider, string $reference): ?PaymentTransaction
    {
        return $this->model
            ->where('provider', $provider)
            ->where('provider_transaction_id', $reference)
            ->first();
    }

    /**
     * Find a captured provider transaction for idempotent payment recording.
     */
    public function findSuccessfulByProviderReference(string $provider, string $reference): ?PaymentTransaction
    {
        return $this->model
            ->where('provider', $provider)
            ->where('provider_transaction_id', $reference)
            ->where('status', 'succeeded')
            ->first();
    }

    /**
     * Captured payments inside a window, for revenue charting.
     */
    public function succeededBetween($from, $to)
    {
        return $this->model->newQuery()
            ->where('status', 'succeeded')
            ->whereBetween('processed_at', [$from, $to])
            ->get(['amount', 'processed_at']);
    }
}
