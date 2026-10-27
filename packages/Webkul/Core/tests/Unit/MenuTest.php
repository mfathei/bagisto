<?php

use Illuminate\Support\Collection;
use Webkul\Core\Menu;
use Webkul\Core\Menu\MenuItem;
use Webkul\User\Facades\Bouncer;

/**
 * Create config for menu items.
 */
beforeEach(function () {
    config()->set('menu.admin', [
        [
            'key' => 'dashboard',
            'name' => 'admin::app.components.layouts.sidebar.dashboard',
            'route' => 'admin.dashboard.index',
            'sort' => 1,
            'icon' => 'icon-dashboard',
        ], [
            'key' => 'sales',
            'name' => 'admin::app.components.layouts.sidebar.sales',
            'route' => 'admin.sales.orders.index',
            'sort' => 2,
            'icon' => 'icon-sales',
        ], [
            'key' => 'sales.orders',
            'name' => 'admin::app.components.layouts.sidebar.orders',
            'route' => 'admin.sales.orders.index',
            'sort' => 1,
            'icon' => '',
        ],
    ]);
});

it('should add and get menu items', function () {
    $menu = new Menu;

    foreach (config('menu.admin') as $menuItem) {
        $menu->addItem(new MenuItem(
            key: $menuItem['key'],
            name: trans($menuItem['name']),
            route: $menuItem['route'],
            sort: $menuItem['sort'],
            icon: $menuItem['icon'],
            children: collect([]),
        ));
    }

    $menuItems = $menu->getItems('admin');

    expect($menuItems->first()->key)->toBe('dashboard');

    expect($menuItems->first()->name)->toBe(trans('admin::app.components.layouts.sidebar.dashboard'));

    expect($menuItems->first()->route)->toBe('admin.dashboard.index');

    expect($menuItems->first()->sort)->toBe(1);

    expect($menuItems->first()->icon)->toBe('icon-dashboard');

    expect($menuItems->last()->key)->toBe('sales');
});

it('should process sub menu items', function () {
    $menu = new Menu;

    $class = new ReflectionClass(Menu::class);

    $method = $class->getMethod('processSubMenuItems');

    $subMenuItems = $method->invoke($menu, config('menu.admin'));

    expect($subMenuItems)->toBeInstanceOf(Collection::class);

    expect($subMenuItems->count())->toBe(3);

    expect($subMenuItems->first())->toBeInstanceOf(MenuItem::class);

    expect($subMenuItems->first()->key)->toBe('dashboard');

    expect($subMenuItems->first()->name)->toBe(trans('admin::app.components.layouts.sidebar.dashboard'));

    expect($subMenuItems->first()->route)->toBe('admin.dashboard.index');

    expect($subMenuItems->first()->sort)->toBe(1);

    expect($subMenuItems->first()->icon)->toBe('icon-dashboard');

    expect($subMenuItems->last()->key)->toBe('sales');

    expect($subMenuItems->last()->name)->toBe(trans('admin::app.components.layouts.sidebar.sales'));
});

// ==== Skipped Levels ====

it('lifts sub menu items whose parent level holds no menu item', function () {
    Bouncer::shouldReceive('hasPermission')->andReturn(true);

    config()->set('menu.admin', [
        [
            'key' => 'saas',
            'name' => 'admin::app.components.layouts.sidebar.sales',
            'route' => 'admin.dashboard.index',
            'sort' => 1,
            'icon' => 'icon-settings',
        ], [
            'key' => 'saas.tenant.dashboard',
            'name' => 'admin::app.components.layouts.sidebar.dashboard',
            'route' => 'admin.dashboard.index',
            'sort' => 1,
            'icon' => '',
        ], [
            'key' => 'saas.tenant.customers',
            'name' => 'admin::app.components.layouts.sidebar.customers',
            'route' => 'admin.customers.customers.index',
            'sort' => 2,
            'icon' => '',
        ],
    ]);

    $items = (new Menu)->getItems('admin');

    expect($items)->toHaveCount(1)
        ->and($items->first()->getKey())->toBe('saas')
        ->and($items->first()->getChildren()->pluck('key')->all())
        ->toBe(['saas.tenant.dashboard', 'saas.tenant.customers']);
});

it('lifts sub menu items to the top when their section holds no menu item', function () {
    Bouncer::shouldReceive('hasPermission')->andReturnUsing(fn ($key) => $key !== 'saas');

    config()->set('menu.admin', [
        [
            'key' => 'saas',
            'name' => 'admin::app.components.layouts.sidebar.sales',
            'route' => 'admin.dashboard.index',
            'sort' => 1,
            'icon' => 'icon-settings',
        ], [
            'key' => 'saas.tenant.dashboard',
            'name' => 'admin::app.components.layouts.sidebar.dashboard',
            'route' => 'admin.dashboard.index',
            'sort' => 1,
            'icon' => '',
        ], [
            'key' => 'saas.tenant.customers',
            'name' => 'admin::app.components.layouts.sidebar.customers',
            'route' => 'admin.customers.customers.index',
            'sort' => 2,
            'icon' => '',
        ],
    ]);

    $items = (new Menu)->getItems('admin');

    expect($items->pluck('key')->all())->toBe([
        'saas.tenant.dashboard',
        'saas.tenant.customers',
    ]);
});
