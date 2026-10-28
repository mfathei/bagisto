<?php

use Webkul\BookingProduct\Models\Booking;
use Webkul\CartRule\Models\CartRule;
use Webkul\CatalogRule\Models\CatalogRule;
use Webkul\Category\Models\Category;
use Webkul\Checkout\Models\Cart;
use Webkul\CMS\Models\Page;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\Wishlist;
use Webkul\EUWithdrawal\Models\Withdrawal;
use Webkul\GDPR\Models\GDPRDataRequest;
use Webkul\Inventory\Models\InventorySource;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductReview;
use Webkul\RMA\Models\RMA;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderTransaction;
use Webkul\Sales\Models\Refund;
use Webkul\Sales\Models\Shipment;
use Webkul\User\Models\Admin;

return [
    /*
    |--------------------------------------------------------------------------
    | Module switch
    |--------------------------------------------------------------------------
    |
    | When disabled the platform behaves like a standard Bagisto installation.
    | Tenant tables and channels remain, but host resolution and tenant scoping
    | are skipped.
    |
    */

    'enabled' => env('SAAS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Platform identity
    |--------------------------------------------------------------------------
    |
    | The platform host serves registration, plan listing and the Super Admin
    | panel. It is never resolved as a tenant storefront.
    |
    */

    'platform_name' => env('SAAS_PLATFORM_NAME', 'Bagisto SaaS'),

    'platform_domain' => env('SAAS_PLATFORM_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'),

    'platform_hosts' => array_values(array_filter(array_map('trim', explode(',', (string) env('SAAS_PLATFORM_HOSTS', ''))))),

    'super_admin_prefix' => env('SAAS_SUPER_ADMIN_PREFIX', 'super-admin'),

    'super_admin_session_key' => 'saas_super_admin_session',

    /*
    |--------------------------------------------------------------------------
    | Domain resolution
    |--------------------------------------------------------------------------
    */

    'domains' => [
        'suffix' => env('SAAS_DOMAIN_SUFFIX', env('SAAS_PLATFORM_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost')),

        'allow_custom' => env('SAAS_ALLOW_CUSTOM_DOMAINS', true),

        'auto_verify_platform_subdomains' => true,

        'dns_record_type' => 'TXT',

        'challenge_ttl' => 60 * 60 * 24,

        'verification_cache_ttl' => 60,

        'reserved_subdomains' => [
            'admin',
            'administrator',
            'api',
            'app',
            'assets',
            'auth',
            'billing',
            'blog',
            'cdn',
            'checkout',
            'connect',
            'dashboard',
            'dev',
            'docs',
            'download',
            'email',
            'files',
            'ftp',
            'help',
            'imap',
            'internal',
            'localhost',
            'mail',
            'media',
            'my',
            'ns1',
            'ns2',
            'panel',
            'pay',
            'payment',
            'portal',
            'register',
            'root',
            'saas',
            'secure',
            'shop',
            'smtp',
            'ssl',
            'static',
            'status',
            'store',
            'stores',
            'support',
            'super-admin',
            'system',
            'test',
            'webmail',
            'www',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resolution cache
    |--------------------------------------------------------------------------
    |
    | Only successful and negative host lookups are cached; the cache is
    | invalidated whenever a domain or tenant changes.
    |
    */

    'cache' => [
        'store' => env('SAAS_CACHE_STORE', null),
        'prefix' => 'saas',
        'ttl' => env('SAAS_CACHE_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Registration and approval
    |--------------------------------------------------------------------------
    */

    'registration' => [
        'enabled' => env('SAAS_REGISTRATION_ENABLED', true),

        'require_email_verification' => env('SAAS_REQUIRE_EMAIL_VERIFICATION', true),

        'require_approval' => env('SAAS_REQUIRE_APPROVAL', true),

        'default_plan' => env('SAAS_DEFAULT_PLAN', 'free'),

        'trial_days' => env('SAAS_TRIAL_DAYS', 14),

        'password_min_length' => 12,

        'verification_token_ttl' => 60 * 60 * 48,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant lifecycle
    |--------------------------------------------------------------------------
    */

    'tenants' => [
        'default_timezone' => env('SAAS_DEFAULT_TIMEZONE', 'UTC'),

        'default_locale' => env('SAAS_DEFAULT_LOCALE', 'en'),

        'default_currency' => env('SAAS_DEFAULT_CURRENCY', 'USD'),

        'slug_regex' => '/^[a-z0-9](?:[a-z0-9-]{1,28}[a-z0-9])?$/',

        'export_disk' => env('SAAS_EXPORT_DISK', 'local'),

        'export_prefix' => 'saas/exports',

        'soft_delete_retention_days' => env('SAAS_TENANT_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing
    |--------------------------------------------------------------------------
    |
    | The manual provider records invoices and payments entered by a Super
    | Admin. No gateway is configured out of the box; the adapter contract in
    | App\Contracts\BillingProvider is the integration point.
    |
    */

    'billing' => [
        'provider' => env('SAAS_BILLING_PROVIDER', 'manual'),

        'currency' => env('SAAS_BILLING_CURRENCY', 'USD'),

        'grace_days' => env('SAAS_BILLING_GRACE_DAYS', 3),

        'invoice_due_days' => env('SAAS_INVOICE_DUE_DAYS', 7),

        'mrr_is_estimate' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Entitlements
    |--------------------------------------------------------------------------
    |
    | Quota enforcement is server-side. A plan limit of null means unlimited.
    | When a tenant is over quota as the result of a downgrade, new resource
    | creation is blocked but existing resources are never deleted.
    |
    */

    'entitlements' => [
        'features' => [
            'max_products' => 'Maximum products',
            'max_staff' => 'Maximum staff accounts',
            'monthly_orders' => 'Monthly order limit',
            'storage_mb' => 'Storage quota (MB)',
            'custom_domain' => 'Custom domains',
            'advanced_analytics' => 'Advanced analytics',
            'api_access' => 'API access',
            'premium_support' => 'Premium support',
        ],

        'grace_overage_percent' => env('SAAS_QUOTA_GRACE_PERCENT', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant role
    |--------------------------------------------------------------------------
    |
    | A tenant administrator is a normal Bagisto admin (admins.tenant_id) whose
    | role holds only these ACL keys. Platform settings, channels, inventory
    | sources and the platform user/role screens are deliberately excluded.
    |
    | Modules whose tables are still platform-wide are read-only or withheld:
    | attributes and attribute families are global schema, customer groups are
    | shared pricing buckets, and marketing communications plus search/SEO
    | write to tables without tenant ownership. RMA configuration is withheld
    | for the same reason while order-bound RMA records are tenant-scoped.
    |
    */

    'tenant_role_permissions' => [
        'dashboard',
        'sales',
        'sales.orders',
        'sales.orders.create',
        'sales.orders.view',
        'sales.orders.cancel',
        'sales.invoices',
        'sales.invoices.view',
        'sales.invoices.create',
        'sales.invoices.update',
        'sales.shipments',
        'sales.shipments.view',
        'sales.shipments.create',
        'sales.refunds',
        'sales.refunds.view',
        'sales.refunds.create',
        'sales.transactions',
        'sales.transactions.view',
        'sales.rma',
        'sales.rma.requests',
        'sales.rma.requests.create',
        'sales.rma.reasons',
        'sales.rma.rules',
        'sales.rma.statuses',
        'sales.rma.custom-fields',
        'sales.eu_withdrawals',
        'sales.eu_withdrawals.view',
        'sales.eu_withdrawals.decline',
        'sales.eu_withdrawals.mark_refunded',
        'sales.eu_withdrawals.resend_confirmation',
        'sales.bookings',
        'catalog',
        'catalog.products',
        'catalog.products.create',
        'catalog.products.copy',
        'catalog.products.edit',
        'catalog.products.delete',
        'catalog.categories',
        'catalog.categories.create',
        'catalog.categories.edit',
        'catalog.categories.delete',
        'catalog.attributes',
        'catalog.families',
        'customers',
        'customers.customers',
        'customers.customers.create',
        'customers.customers.edit',
        'customers.customers.delete',
        'customers.customers.login_as_customer',
        'customers.addresses',
        'customers.addresses.create',
        'customers.addresses.edit',
        'customers.addresses.delete',
        'customers.note',
        'customers.groups',
        'customers.reviews',
        'customers.reviews.edit',
        'customers.reviews.delete',
        'customers.gdpr_requests',
        'customers.gdpr_requests.edit',
        'customers.gdpr_requests.delete',
        'marketing',
        'marketing.promotions',
        'marketing.promotions.cart_rules',
        'marketing.promotions.cart_rules.create',
        'marketing.promotions.cart_rules.copy',
        'marketing.promotions.cart_rules.edit',
        'marketing.promotions.cart_rules.delete',
        'marketing.promotions.catalog_rules',
        'marketing.promotions.catalog_rules.create',
        'marketing.promotions.catalog_rules.edit',
        'marketing.promotions.catalog_rules.delete',
        'reporting',
        'reporting.sales',
        'reporting.customers',
        'reporting.products',
        'cms',
        'cms.create',
        'cms.edit',
        'cms.delete',
        'saas.tenant.dashboard',
        'saas.tenant.team',
        'saas.tenant.billing',
        'saas.tenant.usage',
        'saas.tenant.domains',
        'saas.tenant.settings',
        'saas.tenant.onboarding',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant-owned models
    |--------------------------------------------------------------------------
    |
    | These Eloquent models receive a tenant global scope and automatic
    | tenant_id assignment whenever a tenant context is active. The list is
    | intentionally explicit so platform-owned data stays visible.
    |
    */

    'tenant_owned_models' => [
        Admin::class,
        Product::class,
        Category::class,
        Customer::class,
        Cart::class,
        Order::class,
        Invoice::class,
        Shipment::class,
        Refund::class,
        OrderTransaction::class,
        InventorySource::class,
        Page::class,
        CartRule::class,
        CatalogRule::class,
        Wishlist::class,
        ProductReview::class,
        Booking::class,
        Withdrawal::class,
        GDPRDataRequest::class,
        RMA::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | API
    |--------------------------------------------------------------------------
    */

    'api' => [
        'prefix' => 'api/v1/saas',

        'rate_limit' => env('SAAS_API_RATE_LIMIT', 60),

        'registration_rate_limit' => env('SAAS_REGISTRATION_RATE_LIMIT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'from' => env('SAAS_MAIL_FROM', env('MAIL_FROM_ADDRESS', 'hello@example.com')),

        'trial_warning_days' => [7, 3, 1],

        'queue' => env('SAAS_NOTIFICATION_QUEUE', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform settings defaults
    |--------------------------------------------------------------------------
    |
    | Super Admins can override these at runtime; the stored value wins.
    |
    */

    'settings' => [
        'platform_name' => env('SAAS_PLATFORM_NAME', 'Bagisto SaaS'),
        'support_email' => env('SAAS_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
        'registration_enabled' => true,
        'require_approval' => true,
        'require_email_verification' => true,
        'default_trial_days' => 14,
        'default_plan_slug' => 'free',
        'maintenance_message' => '',
        'data_retention_days' => 30,
        'default_product_limit' => 100,
        'default_staff_limit' => 5,
    ],
];
