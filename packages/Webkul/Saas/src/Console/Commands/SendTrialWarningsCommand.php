<?php

namespace Webkul\Saas\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Webkul\Saas\Enums\SubscriptionStatus;
use Webkul\Saas\Mail\TrialEndingMail;
use Webkul\Saas\Repositories\SubscriptionRepository;

class SendTrialWarningsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:send-trial-warnings
                            {--dry-run : Report the tenants that would be notified without sending or recording anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue trial-ending reminders once per configured warning threshold.';

    /**
     * Execute the console command.
     */
    public function handle(SubscriptionRepository $subscriptions): int
    {
        $thresholds = collect((array) config('saas.notifications.trial_warning_days', [7, 3, 1]))
            ->map(fn ($days) => (int) $days)
            ->filter(fn (int $days) => $days > 0)
            ->sort()
            ->values();

        if ($thresholds->isEmpty()) {
            $this->components->info('No trial warning thresholds are configured.');

            return self::SUCCESS;
        }

        $due = $subscriptions->getModel()
            ->newQuery()
            ->with(['tenant', 'plan'])
            ->where('status', SubscriptionStatus::Trialing->value)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->where('trial_ends_at', '<=', now()->addDays($thresholds->max()))
            ->orderBy('trial_ends_at')
            ->get();

        $notified = 0;
        $skipped = 0;
        $failures = 0;
        $dryRun = (bool) $this->option('dry-run');

        foreach ($due as $subscription) {
            $tenant = $subscription->tenant;

            if (! $tenant || ! $tenant->email) {
                $skipped++;

                continue;
            }

            $daysRemaining = max(1, (int) ceil(now()->diffInDays($subscription->trial_ends_at, false)));

            $metadata = (array) $subscription->metadata;
            $alreadySent = array_map('intval', (array) ($metadata['trial_warnings'] ?? []));

            $threshold = $thresholds->first(
                fn (int $days) => $daysRemaining <= $days && ! in_array($days, $alreadySent, true)
            );

            if ($threshold === null) {
                $skipped++;

                continue;
            }

            if ($dryRun) {
                $this->table(
                    ['Tenant', 'Email', 'Trial ends', 'Days remaining'],
                    [[$tenant->slug, $tenant->email, $subscription->trial_ends_at?->toDateTimeString(), $daysRemaining]]
                );

                $notified++;

                continue;
            }

            try {
                Mail::to($tenant->email)
                    ->queue((new TrialEndingMail($tenant, $daysRemaining))->onQueue(
                        (string) config('saas.notifications.queue', 'default')
                    ));
            } catch (Throwable $exception) {
                $failures++;
                $this->components->warn('Trial reminder failed for '.$tenant->slug.': '.$exception->getMessage());

                continue;
            }

            $metadata['trial_warnings'] = array_values(array_unique(array_merge(
                $alreadySent,
                $thresholds->filter(fn (int $days) => $days >= $daysRemaining)->all()
            )));

            $subscription->forceFill(['metadata' => $metadata])->save();

            $notified++;
        }

        $this->components->success(
            ($dryRun ? $notified.' tenant(s) would be notified.' : $notified.' trial reminder(s) queued.')
            .' '.$skipped.' skipped.'
        );

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
