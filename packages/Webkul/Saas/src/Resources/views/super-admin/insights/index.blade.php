@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.insights.title'))

@section('content')
    @php
        $currency = config('saas.billing.currency', 'USD');

        $signupsMax = max(1, (int) max(array_column($signups, 'value') ?: [0]));

        $signupsTotal = array_sum(array_column($signups, 'value'));

        $labelStep = max(1, (int) ceil(count($signups) / 7));
    @endphp

    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.nav.insights')</span>
            </div>

            <h1>@lang('saas::app.super-admin.insights.title')</h1>

            <p>@lang('saas::app.super-admin.insights.subtitle')</p>
        </div>
    </div>

    <div class="stats-grid cols-4">
        <div class="stat-card">
            <span class="stat-icon">
                @include('saas::layouts.partials.icon', ['name' => 'tenants'])
            </span>

            <div class="stat-content">
                <p class="stat-value">{{ array_sum($statusCounts) }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.dashboard.total-tenants')</span>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon green">
                @include('saas::layouts.partials.icon', ['name' => 'check'])
            </span>

            <div class="stat-content">
                <p class="stat-value">{{ $statusCounts['active'] ?? 0 }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.dashboard.active-tenants')</span>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon purple">
                @include('saas::layouts.partials.icon', ['name' => 'subscriptions'])
            </span>

            <div class="stat-content">
                <p class="stat-value">{{ $activeSubscriptions }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.dashboard.active-subscriptions')</span>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon amber">
                @include('saas::layouts.partials.icon', ['name' => 'plans'])
            </span>

            <div class="stat-content">
                <p class="stat-value">{{ $trialingSubscriptions }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.dashboard.trial-subscriptions')</span>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon">
                @include('saas::layouts.partials.icon', ['name' => 'insights'])
            </span>

            <div class="stat-content">
                <p class="stat-value">{{ $currency }} {{ number_format($mrr, 2) }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.dashboard.mrr')</span>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon purple">
                @include('saas::layouts.partials.icon', ['name' => 'insights'])
            </span>

            <div class="stat-content">
                <p class="stat-value">{{ $currency }} {{ number_format($arr, 2) }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.insights.arr')</span>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon green">
                @include('saas::layouts.partials.icon', ['name' => 'invoices'])
            </span>

            <div class="stat-content">
                <p class="stat-value">{{ $currency }} {{ number_format($collected, 2) }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.insights.collected')</span>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon red">
                @include('saas::layouts.partials.icon', ['name' => 'alert'])
            </span>

            <div class="stat-content">
                <p class="stat-value">{{ $currency }} {{ number_format($outstanding, 2) }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.insights.outstanding')</span>
            </div>
        </div>
    </div>

    <div class="dashboard-cols">
        <div class="dashboard-main">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.dashboard.signups-chart')</h2>

                        <p>@lang('saas::app.super-admin.insights.signups-subtitle')</p>
                    </div>
                </div>

                <div class="chart">
                    @if ($signupsTotal > 0)
                        <div class="chart-bars">
                            @foreach ($signups as $bucket)
                                <div class="chart-col">
                                    <span
                                        class="chart-bar {{ $bucket['value'] > 0 ? '' : 'zero' }}"
                                        style="height: {{ $bucket['value'] > 0 ? max(3, (int) round($bucket['value'] / $signupsMax * 100)) : 3 }}%"
                                        title="{{ $bucket['label'] }}: {{ $bucket['value'] }}"
                                    ></span>
                                </div>
                            @endforeach
                        </div>

                        <div class="chart-labels">
                            @foreach ($signups as $bucket)
                                <span>{{ $loop->index % $labelStep === 0 ? $bucket['label'] : '' }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="chart-empty">
                            @include('saas::layouts.partials.icon', ['name' => 'customers'])

                            <span>@lang('saas::app.super-admin.dashboard.no-chart-data')</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.insights.catalogue')</h2>

                        <p>@lang('saas::app.super-admin.insights.catalogue-help')</p>
                    </div>
                </div>

                <div class="tiles-grid quad" style="padding: 18px;">
                    @foreach ([
                        ['key' => 'products', 'icon' => 'products', 'label' => trans('saas::app.super-admin.tenants.products')],
                        ['key' => 'categories', 'icon' => 'cms', 'label' => trans('saas::app.super-admin.tenants.categories')],
                        ['key' => 'customers', 'icon' => 'customers', 'label' => trans('saas::app.super-admin.tenants.customers')],
                        ['key' => 'orders', 'icon' => 'orders', 'label' => trans('saas::app.super-admin.tenants.orders')],
                    ] as $tile)
                        <div class="card info-tile">
                            <span class="info-label">{{ $tile['label'] }}</span>

                            <p class="info-value">{{ number_format((int) ($platformTotals[$tile['key']] ?? 0)) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.insights.recent-subscriptions')</h2>
                    </div>

                    <a href="{{ route('saas.super-admin.subscriptions.index') }}">@lang('saas::app.super-admin.dashboard.view-all')</a>
                </div>

                @if ($recentSubscriptions->isEmpty())
                    <div class="empty-block">
                        @include('saas::layouts.partials.icon', ['name' => 'subscriptions'])

                        <p>@lang('saas::app.common.none')</p>
                    </div>
                @else
                    <div class="table-wrap" style="border: 0; border-radius: 0;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>@lang('saas::app.common.tenant')</th>
                                    <th>@lang('saas::app.common.plan')</th>
                                    <th>@lang('saas::app.common.status')</th>
                                    <th>@lang('saas::app.super-admin.subscriptions.period-end')</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($recentSubscriptions as $subscription)
                                    <tr>
                                        <td>
                                            <a class="strong" href="{{ route('saas.super-admin.subscriptions.show', $subscription->getKey()) }}">
                                                {{ $subscription->tenant?->name ?? '#'.$subscription->tenant_id }}
                                            </a>
                                        </td>
                                        <td>{{ $subscription->plan?->name ?? trans('saas::app.common.none') }}</td>
                                        <td>
                                            <span class="badge {{ $subscription->status->value }}">{{ $subscription->status->value }}</span>
                                        </td>
                                        <td class="nowrap">{{ $subscription->current_period_end?->format('M d, Y') ?? trans('saas::app.common.none') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="dashboard-side">
            <div class="card">
                <div class="card-header">
                    <h2>@lang('saas::app.super-admin.dashboard.plan-distribution')</h2>
                </div>

                @if ($planDistribution->isEmpty())
                    <div class="empty-block">
                        @include('saas::layouts.partials.icon', ['name' => 'plans'])

                        <p>@lang('saas::app.common.none')</p>
                    </div>
                @else
                    <ul class="checklist">
                        @foreach ($planDistribution as $row)
                            <li>
                                <span class="strong">{{ $row['name'] }}</span>

                                <strong>@lang('saas::app.super-admin.dashboard.subscriptions-count', ['count' => $row['count']])</strong>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>@lang('saas::app.super-admin.insights.tenant-status')</h2>
                </div>

                <ul class="checklist">
                    @foreach (\Webkul\Saas\Enums\TenantStatus::cases() as $status)
                        <li>
                            <span class="badge {{ $status->value }}">{{ $status->label() }}</span>

                            <strong>{{ $statusCounts[$status->value] ?? 0 }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
