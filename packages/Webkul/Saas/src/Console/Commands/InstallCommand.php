<?php

namespace Webkul\Saas\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Services\PlatformSettings;

class InstallCommand extends Command
{
    /**
     * The default plan definitions seeded on first install.
     *
     * @var array<int, array<string, mixed>>
     */
    protected const PLANS = [
        [
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Evaluate the platform with a single storefront and core commerce features.',
            'price' => 0,
            'currency' => 'USD',
            'billing_interval' => 'monthly',
            'trial_days' => 0,
            'limits' => [
                'max_products' => 100,
                'max_staff' => 3,
                'monthly_orders' => 100,
                'storage_mb' => 512,
            ],
            'features' => [
                'custom_domain' => false,
                'advanced_analytics' => false,
                'api_access' => false,
                'premium_support' => false,
            ],
            'is_active' => true,
            'is_public' => true,
            'sort_order' => 1,
        ],
        [
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'A growing storefront with a custom domain and API access.',
            'price' => 29,
            'currency' => 'USD',
            'billing_interval' => 'monthly',
            'trial_days' => 14,
            'limits' => [
                'max_products' => 1000,
                'max_staff' => 10,
                'monthly_orders' => 5000,
                'storage_mb' => 5120,
            ],
            'features' => [
                'custom_domain' => true,
                'advanced_analytics' => false,
                'api_access' => true,
                'premium_support' => false,
            ],
            'is_active' => true,
            'is_public' => true,
            'sort_order' => 2,
        ],
        [
            'name' => 'Growth',
            'slug' => 'growth',
            'description' => 'High-volume operations with analytics and premium support.',
            'price' => 99,
            'currency' => 'USD',
            'billing_interval' => 'monthly',
            'trial_days' => 14,
            'limits' => [
                'max_products' => 10000,
                'max_staff' => 50,
                'monthly_orders' => 50000,
                'storage_mb' => 20480,
            ],
            'features' => [
                'custom_domain' => true,
                'advanced_analytics' => true,
                'api_access' => true,
                'premium_support' => true,
            ],
            'is_active' => true,
            'is_public' => true,
            'sort_order' => 3,
        ],
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the default subscription plans and platform settings without overwriting existing records.';

    /**
     * Execute the console command.
     */
    public function handle(PlanRepository $plans, PlatformSettings $settings): int
    {
        $created = [];

        foreach (self::PLANS as $definition) {
            if ($plans->findBySlug($definition['slug'])) {
                continue;
            }

            $created[] = $plans->create($definition);
        }

        $this->seedSettings($settings);

        if ($created === []) {
            $this->components->info('All default plans are already present; no plans were created.');
        } else {
            $this->table(
                ['Plan', 'Slug', 'Price', 'Interval'],
                array_map(fn ($plan) => [$plan->name, $plan->slug, $plan->price, $plan->billing_interval], $created)
            );

            $this->components->success(count($created).' default plan(s) created.');
        }

        $this->components->info('Next step: php artisan saas:create-super-admin');

        return self::SUCCESS;
    }

    /**
     * Persist platform setting defaults that are not stored yet.
     */
    protected function seedSettings(PlatformSettings $settings): void
    {
        $stored = $settings->all();

        $defaults = [
            'platform_name' => (string) config('saas.settings.platform_name'),
            'support_email' => (string) config('saas.settings.support_email'),
            'registration_enabled' => (bool) config('saas.settings.registration_enabled'),
            'require_approval' => (bool) config('saas.settings.require_approval'),
            'require_email_verification' => (bool) config('saas.settings.require_email_verification'),
            'default_trial_days' => (int) config('saas.settings.default_trial_days'),
            'default_plan_slug' => (string) config('saas.settings.default_plan_slug'),
            'maintenance_message' => (string) config('saas.settings.maintenance_message'),
            'data_retention_days' => (int) config('saas.settings.data_retention_days'),
            'default_product_limit' => (int) config('saas.settings.default_product_limit'),
            'default_staff_limit' => (int) config('saas.settings.default_staff_limit'),
        ];

        $missing = array_filter(
            $defaults,
            fn ($value, string $key) => ! array_key_exists($key, $stored),
            ARRAY_FILTER_USE_BOTH
        );

        if ($missing !== []) {
            $settings->setMany($missing, 'platform');
        }
    }
}
