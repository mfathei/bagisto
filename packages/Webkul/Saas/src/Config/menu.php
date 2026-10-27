<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SaaS tenant navigation
    |--------------------------------------------------------------------------
    |
    | Displayed inside the regular admin panel for tenant administrators. The
    | sidebar filters each entry through the ACL keys above.
    |
    */
    [
        'key' => 'saas',
        'name' => 'saas::app.admin.menu.saas',
        'route' => 'saas.tenant.dashboard',
        'sort' => 9,
        'icon' => 'icon-settings',
    ], [
        'key' => 'saas.tenant.dashboard',
        'name' => 'saas::app.admin.menu.dashboard',
        'route' => 'saas.tenant.dashboard',
        'sort' => 1,
        'icon' => '',
    ], [
        'key' => 'saas.tenant.team',
        'name' => 'saas::app.admin.menu.team',
        'route' => 'saas.tenant.team.index',
        'sort' => 2,
        'icon' => '',
    ], [
        'key' => 'saas.tenant.domains',
        'name' => 'saas::app.admin.menu.domains',
        'route' => 'saas.tenant.domains.index',
        'sort' => 3,
        'icon' => '',
    ], [
        'key' => 'saas.tenant.billing',
        'name' => 'saas::app.admin.menu.billing',
        'route' => 'saas.tenant.billing.index',
        'sort' => 4,
        'icon' => '',
    ], [
        'key' => 'saas.tenant.usage',
        'name' => 'saas::app.admin.menu.usage',
        'route' => 'saas.tenant.usage.index',
        'sort' => 5,
        'icon' => '',
    ], [
        'key' => 'saas.tenant.settings',
        'name' => 'saas::app.admin.menu.settings',
        'route' => 'saas.tenant.settings.index',
        'sort' => 6,
        'icon' => '',
    ],
];
