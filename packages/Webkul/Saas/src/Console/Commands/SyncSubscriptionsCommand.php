<?php

namespace Webkul\Saas\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Saas\Services\BillingService;
use Webkul\Saas\Services\SubscriptionService;

class SyncSubscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:sync-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Advance subscription lifecycles, invoice elapsed periods and flag overdue invoices.';

    /**
     * Execute the console command.
     */
    public function handle(SubscriptionService $subscriptions, BillingService $billing): int
    {
        $processed = $subscriptions->processLifecycle();
        $invoiced = $billing->invoiceDuePeriods();
        $overdue = $billing->markOverdueInvoices();

        $this->table(
            ['Lifecycle transitions', 'Invoices created', 'Invoices marked overdue'],
            [[$processed, $invoiced, $overdue]]
        );

        $this->components->success('Subscription synchronization completed.');

        return self::SUCCESS;
    }
}
