<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\BillingRecord;

class BillingRecordRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return BillingRecord::class;
    }

    /**
     * Billing records of a tenant, newest first.
     */
    public function forTenant(int $tenantId)
    {
        return $this->model
            ->with('subscription.plan')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Generate the next sequential invoice number.
     */
    public function nextNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ym').'-';

        $last = $this->model
            ->where('number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('number');

        $sequence = $last ? ((int) substr($last, -6)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Outstanding invoices for the platform.
     */
    public function outstanding()
    {
        return $this->model
            ->with('tenant')
            ->whereIn('status', ['open', 'past_due'])
            ->orderBy('due_at')
            ->get();
    }

    /**
     * The unpaid balance of a tenant across open and past due invoices.
     */
    public function outstandingForTenant(int $tenantId): float
    {
        $invoices = $this->model
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['open', 'past_due']);

        return max(0, (float) $invoices->sum('total') - (float) $invoices->sum('amount_paid'));
    }

    /**
     * The most recent invoices, with the tenant billed.
     */
    public function recent(int $limit = 5)
    {
        return $this->model->newQuery()
            ->with(['tenant'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * The unpaid balance across all open and past due invoices.
     */
    public function outstandingBalance(): float
    {
        $invoices = $this->model->newQuery()
            ->whereIn('status', ['open', 'past_due']);

        return max(0, round((float) $invoices->sum('total') - (float) $invoices->sum('amount_paid'), 2));
    }
}
