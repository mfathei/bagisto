<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SaaS tenant area
    |--------------------------------------------------------------------------
    |
    | Every tenant panel route is mapped here because the admin panel refuses
    | any route missing from the ACL map. The keys are part of the restricted
    | role created for each tenant.
    |
    */
    [
        'key' => 'saas.tenant.dashboard',
        'name' => 'saas::app.admin.acl.dashboard',
        'route' => [
            'saas.tenant.dashboard',
        ],
        'sort' => 1,
    ], [
        'key' => 'saas.tenant.team',
        'name' => 'saas::app.admin.acl.team',
        'route' => [
            'saas.tenant.team.index',
            'saas.tenant.team.store',
            'saas.tenant.team.update',
            'saas.tenant.team.destroy',
            'saas.tenant.team.resend',
        ],
        'sort' => 2,
    ], [
        'key' => 'saas.tenant.domains',
        'name' => 'saas::app.admin.acl.domains',
        'route' => [
            'saas.tenant.domains.index',
            'saas.tenant.domains.store',
            'saas.tenant.domains.verify',
            'saas.tenant.domains.primary',
            'saas.tenant.domains.destroy',
        ],
        'sort' => 3,
    ], [
        'key' => 'saas.tenant.billing',
        'name' => 'saas::app.admin.acl.billing',
        'route' => [
            'saas.tenant.billing.index',
        ],
        'sort' => 4,
    ], [
        'key' => 'saas.tenant.usage',
        'name' => 'saas::app.admin.acl.usage',
        'route' => [
            'saas.tenant.usage.index',
        ],
        'sort' => 5,
    ], [
        'key' => 'saas.tenant.settings',
        'name' => 'saas::app.admin.acl.settings',
        'route' => [
            'saas.tenant.settings.index',
            'saas.tenant.settings.update',
        ],
        'sort' => 6,
    ], [
        'key' => 'saas.tenant.onboarding',
        'name' => 'saas::app.admin.acl.onboarding',
        'route' => [
            'saas.tenant.onboarding.update',
        ],
        'sort' => 7,
    ],
];
