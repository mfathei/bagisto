<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public website defaults
    |--------------------------------------------------------------------------
    |
    | Values stored through the Super Admin settings screen override these
    | defaults. Nothing here contains company information that has not been
    | configured by the platform owner.
    |
    */

    'defaults' => [
        'brand_color' => '#4338ca',
        'support_email' => null,
        'contact_phone' => null,
        'banner' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    |
    | Public links rendered in the header and footer. Labels are translation
    | keys so both English and Arabic stay in sync.
    |
    */

    'navigation' => [
        ['route' => 'saas.marketing.features', 'label' => 'saas::marketing.nav.features'],
        ['route' => 'saas.marketing.solutions', 'label' => 'saas::marketing.nav.solutions'],
        ['route' => 'saas.marketing.pricing', 'label' => 'saas::marketing.nav.pricing'],
        ['route' => 'saas.marketing.resources', 'label' => 'saas::marketing.nav.resources'],
        ['route' => 'saas.marketing.about', 'label' => 'saas::marketing.nav.about'],
        ['route' => 'saas.marketing.contact', 'label' => 'saas::marketing.nav.contact'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Each entry maps to an icon rendered by the marketing icon component and
    | to a translation block at features.items.{key}.
    |
    */

    'features' => [
        ['key' => 'storefront', 'icon' => 'storefront'],
        ['key' => 'catalog', 'icon' => 'catalog'],
        ['key' => 'orders', 'icon' => 'orders'],
        ['key' => 'inventory', 'icon' => 'inventory'],
        ['key' => 'customers', 'icon' => 'customers'],
        ['key' => 'payments', 'icon' => 'payments'],
        ['key' => 'analytics', 'icon' => 'analytics'],
        ['key' => 'domains', 'icon' => 'domains'],
        ['key' => 'team', 'icon' => 'team'],
        ['key' => 'languages', 'icon' => 'languages'],
        ['key' => 'mobile', 'icon' => 'mobile'],
        ['key' => 'subscriptions', 'icon' => 'subscriptions'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature page groups
    |--------------------------------------------------------------------------
    */

    'feature_groups' => [
        ['key' => 'store-management', 'features' => ['storefront', 'domains', 'team']],
        ['key' => 'catalog-inventory', 'features' => ['catalog', 'inventory']],
        ['key' => 'orders-fulfilment', 'features' => ['orders']],
        ['key' => 'customers', 'features' => ['customers']],
        ['key' => 'payments-shipping', 'features' => ['payments']],
        ['key' => 'analytics', 'features' => ['analytics']],
        ['key' => 'storefront', 'features' => ['languages', 'mobile']],
        ['key' => 'saas', 'features' => ['subscriptions']],
    ],

    /*
    |--------------------------------------------------------------------------
    | Solutions
    |--------------------------------------------------------------------------
    */

    'solutions' => [
        ['key' => 'small-business', 'icon' => 'storefront'],
        ['key' => 'retail', 'icon' => 'catalog'],
        ['key' => 'fashion', 'icon' => 'customers'],
        ['key' => 'electronics', 'icon' => 'inventory'],
        ['key' => 'home-lifestyle', 'icon' => 'orders'],
        ['key' => 'expanding', 'icon' => 'analytics'],
        ['key' => 'migrating', 'icon' => 'domains'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Frequently asked questions
    |--------------------------------------------------------------------------
    */

    'faqs' => [
        'create-store',
        'coding',
        'own-domain',
        'change-plan',
        'manage-store',
        'payments',
        'cancel',
        'support',
    ],

    'pricing_faqs' => [
        'trial',
        'switch',
        'limits',
        'billing',
        'cancel',
        'custom-domain',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legal pages
    |--------------------------------------------------------------------------
    */

    'legal' => [
        'privacy' => ['route' => 'saas.marketing.privacy', 'key' => 'privacy'],
        'terms' => ['route' => 'saas.marketing.terms', 'key' => 'terms'],
        'refund-policy' => ['route' => 'saas.marketing.refund-policy', 'key' => 'refund-policy'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact form
    |--------------------------------------------------------------------------
    */

    'inquiry_types' => ['sales', 'support', 'billing', 'partnership', 'other'],

    'contact_rate_limit' => env('SAAS_CONTACT_RATE_LIMIT', 5),

    /*
    |--------------------------------------------------------------------------
    | Social networks
    |--------------------------------------------------------------------------
    |
    | Only networks with a configured URL are rendered. No accounts are
    | invented by the application.
    |
    */

    'social' => ['facebook', 'x', 'linkedin', 'instagram'],

    /*
    |--------------------------------------------------------------------------
    | Sitemap
    |--------------------------------------------------------------------------
    */

    'sitemap' => [
        ['route' => 'saas.marketing.home', 'priority' => '1.0', 'changefreq' => 'weekly'],
        ['route' => 'saas.marketing.features', 'priority' => '0.9', 'changefreq' => 'monthly'],
        ['route' => 'saas.marketing.solutions', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['route' => 'saas.marketing.pricing', 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['route' => 'saas.marketing.resources', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['route' => 'saas.marketing.about', 'priority' => '0.6', 'changefreq' => 'yearly'],
        ['route' => 'saas.marketing.contact', 'priority' => '0.7', 'changefreq' => 'yearly'],
        ['route' => 'saas.marketing.privacy', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['route' => 'saas.marketing.terms', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['route' => 'saas.marketing.refund-policy', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ],
];
