@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.dashboard.title'))

@php
    $currency = config('saas.billing.currency', 'USD');

    $signupsTotal = array_sum(array_column($signupsPerDay, 'value'));

    $signupsMax = max(1, (int) max(array_column($signupsPerDay, 'value') ?: [0]));

    $revenueTotal = array_sum(array_column($revenuePerDay, 'value'));

    $revenueMax = max(1, (float) max(array_column($revenuePerDay, 'value') ?: [0]));

    $signupLabelStep = max(1, (int) ceil(count($signupsPerDay) / 7));

    $revenueLabelStep = max(1, (int) ceil(count($revenuePerDay) / 7));
@endphp

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.super-admin.dashboard.greeting', ['name' => auth('super_admin')->user()->name ?? ''])</h1>

            <p>@lang('saas::app.super-admin.dashboard.subtitle')</p>
        </div>

        <form class="period-form" method="GET" action="{{ route('saas.super-admin.dashboard') }}">
            <input type="date" name="from" value="{{ $from->toDateString() }}" aria-label="@lang('saas::app.super-admin.dashboard.from')">

            <span class="period-sep">&ndash;</span>

            <input type="date" name="to" value="{{ $to->toDateString() }}" aria-label="@lang('saas::app.super-admin.dashboard.to')">

            <button type="submit" class="btn small">@lang('saas::app.super-admin.dashboard.apply-range')</button>
        </form>
    </div>

    <p class="section-title">@lang('saas::app.super-admin.dashboard.overall-details')</p>

    <div class="card">
        <div class="stat-strip">
            <div class="stat-card">
                <span class="stat-icon">
                    @include('saas::layouts.partials.icon', ['name' => 'tenants'])
                </span>

                <div class="stat-content">
                    <p class="stat-value">{{ $totalTenants }}</p>

                    <span class="stat-label">@lang('saas::app.super-admin.dashboard.total-tenants')</span>

                    @if (! is_null($tenantGrowth))
                        <span class="stat-delta {{ $tenantGrowth >= 0 ? 'up' : 'down' }}">
                            {!! $tenantGrowth >= 0 ? '&uarr;' : '&darr;' !!} {{ abs($tenantGrowth) }}%
                        </span>
                    @elseif ($totalTenants > 0)
                        <span class="stat-delta new">@lang('saas::app.super-admin.dashboard.new-this-period')</span>
                    @endif
                </div>
            </div>

            <div class="stat-card">
                <span class="stat-icon green">
                    @include('saas::layouts.partials.icon', ['name' => 'check'])
                </span>

                <div class="stat-content">
                    <p class="stat-value">{{ $activeTenants }}</p>

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
                    @include('saas::layouts.partials.icon', ['name' => 'invoices'])
                </span>

                <div class="stat-content">
                    <p class="stat-value">{{ $currency }} {{ number_format((float) $totalRevenue, 2) }}</p>

                    <span class="stat-label">@lang('saas::app.super-admin.dashboard.total-revenue')</span>

                    @if (! is_null($revenueGrowth))
                        <span class="stat-delta {{ $revenueGrowth >= 0 ? 'up' : 'down' }}">
                            {!! $revenueGrowth >= 0 ? '&uarr;' : '&darr;' !!} {{ abs($revenueGrowth) }}%
                        </span>
                    @elseif ($totalRevenue > 0)
                        <span class="stat-delta new">@lang('saas::app.super-admin.dashboard.new-this-period')</span>
                    @endif
                </div>
            </div>

            <div class="stat-card">
                <span class="stat-icon red">
                    @include('saas::layouts.partials.icon', ['name' => 'alert'])
                </span>

                <div class="stat-content">
                    <p class="stat-value">{{ $currency }} {{ number_format((float) $dueAmount, 2) }}</p>

                    <span class="stat-label">@lang('saas::app.super-admin.dashboard.due-amount')</span>

                    @if ($pastDueSubscriptions > 0)
                        <span class="stat-delta down">
                            {{ trans_choice('saas::app.super-admin.dashboard.past-due-count', $pastDueSubscriptions, ['count' => $pastDueSubscriptions]) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-cols">
        <div class="dashboard-main">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.dashboard.recent-tenants')</h2>

                        <p>@lang('saas::app.super-admin.dashboard.recent-tenants-subtitle')</p>
                    </div>

                    <a href="{{ route('saas.super-admin.tenants.index') }}">@lang('saas::app.super-admin.dashboard.view-all')</a>
                </div>

                @if ($recentTenants->isEmpty())
                    <div class="empty-block">
                        @include('saas::layouts.partials.icon', ['name' => 'tenants'])

                        <p>@lang('saas::app.common.none')</p>
                    </div>
                @else
                    <ul class="row-list">
                        @foreach ($recentTenants as $tenant)
                            @php
                                $domain = $tenant->domains->firstWhere('is_primary', true) ?? $tenant->domains->firstWhere('status', 'verified');
                            @endphp

                            <li class="row-item with-avatar">
                                <span class="avatar-sm">{{ str($tenant->name)->substr(0, 1)->upper() }}</span>

                                <div class="row-main">
                                    <a class="row-title" href="{{ route('saas.super-admin.tenants.show', $tenant->id) }}">{{ $tenant->name }}</a>

                                    <p class="row-sub">{{ $domain?->hostname ?? $tenant->slug }}</p>
                                </div>

                                <div class="row-side">
                                    <span class="badge {{ $tenant->status->value }}">{{ $tenant->status->label() }}</span>

                                    <p class="row-sub">{{ $tenant->created_at?->format('M d, Y') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.dashboard.recent-invoices')</h2>

                        <p>@lang('saas::app.super-admin.dashboard.recent-invoices-subtitle')</p>
                    </div>

                    <a href="{{ route('saas.super-admin.billing.index') }}">@lang('saas::app.super-admin.dashboard.view-all')</a>
                </div>

                @if ($recentInvoices->isEmpty())
                    <div class="empty-block">
                        @include('saas::layouts.partials.icon', ['name' => 'invoices'])

                        <p>@lang('saas::app.common.none')</p>
                    </div>
                @else
                    <ul class="row-list">
                        @foreach ($recentInvoices as $invoice)
                            <li class="row-item">
                                <div class="row-main">
                                    <a class="row-title" href="{{ route('saas.super-admin.billing.show', $invoice->id) }}">{{ $invoice->number }}</a>

                                    <p class="row-sub">{{ $invoice->tenant?->name ?? trans('saas::app.super-admin.audit.platform') }}</p>
                                </div>

                                <div class="row-side">
                                    <p class="row-amount">{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</p>

                                    <span class="badge {{ $invoice->status->value }}">{{ $invoice->status->label() }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.dashboard.expiring-soon')</h2>

                        <p>@lang('saas::app.super-admin.dashboard.expiring-subtitle', ['days' => 14])</p>
                    </div>

                    <a href="{{ route('saas.super-admin.subscriptions.index') }}">@lang('saas::app.super-admin.dashboard.view-all')</a>
                </div>

                @if ($expiringSoon->isEmpty())
                    <div class="empty-block">
                        @include('saas::layouts.partials.icon', ['name' => 'subscriptions'])

                        <p>@lang('saas::app.super-admin.dashboard.no-expiring', ['days' => 14])</p>
                    </div>
                @else
                    <ul class="row-list">
                        @foreach ($expiringSoon as $subscription)
                            @php
                                $trialEnding = $subscription->trial_ends_at?->between(now(), now()->addDays(14));

                                $endsAt = $trialEnding ? $subscription->trial_ends_at : $subscription->current_period_end;
                            @endphp

                            <li class="row-item">
                                <div class="row-main">
                                    <a class="row-title" href="{{ route('saas.super-admin.subscriptions.show', $subscription->id) }}">{{ $subscription->tenant?->name ?? '#'.$subscription->tenant_id }}</a>

                                    <p class="row-sub">{{ $subscription->plan?->name }}</p>
                                </div>

                                <div class="row-side">
                                    @if ($trialEnding)
                                        <span class="badge trialing">@lang('saas::app.super-admin.dashboard.trial-badge')</span>
                                    @endif

                                    <p class="row-sub">{{ $endsAt?->format('M d, Y') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="dashboard-side">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.dashboard.subscription-stats')</h2>

                        <p>@lang('saas::app.super-admin.dashboard.subscription-stats-subtitle')</p>
                    </div>
                </div>

                <div class="chart-head">
                    <div>
                        <h3>@lang('saas::app.super-admin.dashboard.signups-chart')</h3>

                        <p>{{ $from->format('M d') }} &ndash; {{ $to->format('M d') }}</p>
                    </div>

                    <div class="chart-total">
                        <strong>{{ $signupsTotal }}</strong>

                        <span>@lang('saas::app.super-admin.dashboard.signups')</span>
                    </div>
                </div>

                <div class="chart">
                    @if ($signupsTotal > 0)
                        <div class="chart-bars">
                            @foreach ($signupsPerDay as $bucket)
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
                            @foreach ($signupsPerDay as $bucket)
                                <span>{{ $loop->index % $signupLabelStep === 0 ? $bucket['label'] : '' }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="chart-empty">
                            @include('saas::layouts.partials.icon', ['name' => 'customers'])

                            <span>@lang('saas::app.super-admin.dashboard.no-chart-data')</span>
                        </div>
                    @endif
                </div>

                <div class="chart-head">
                    <div>
                        <h3>@lang('saas::app.super-admin.dashboard.revenue-chart')</h3>

                        <p>{{ $from->format('M d') }} &ndash; {{ $to->format('M d') }}</p>
                    </div>

                    <div class="chart-total">
                        <strong>{{ $currency }} {{ number_format((float) $revenueTotal, 2) }}</strong>

                        <span>@lang('saas::app.super-admin.dashboard.collected')</span>
                    </div>
                </div>

                <div class="chart">
                    @if ($revenueTotal > 0)
                        <div class="chart-bars">
                            @foreach ($revenuePerDay as $bucket)
                                <div class="chart-col">
                                    <span
                                        class="chart-bar alt {{ $bucket['value'] > 0 ? '' : 'zero' }}"
                                        style="height: {{ $bucket['value'] > 0 ? max(3, (int) round($bucket['value'] / $revenueMax * 100)) : 3 }}%"
                                        title="{{ $bucket['label'] }}: {{ $currency }} {{ number_format((float) $bucket['value'], 2) }}"
                                    ></span>
                                </div>
                            @endforeach
                        </div>

                        <div class="chart-labels">
                            @foreach ($revenuePerDay as $bucket)
                                <span>{{ $loop->index % $revenueLabelStep === 0 ? $bucket['label'] : '' }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="chart-empty">
                            @include('saas::layouts.partials.icon', ['name' => 'invoices'])

                            <span>@lang('saas::app.super-admin.dashboard.no-chart-data')</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>@lang('saas::app.super-admin.dashboard.plan-distribution')</h2>
                </div>

                @if ($planCounts->isEmpty())
                    <div class="empty-block">
                        @include('saas::layouts.partials.icon', ['name' => 'plans'])

                        <p>@lang('saas::app.common.none')</p>
                    </div>
                @else
                    <ul class="checklist">
                        @foreach ($planCounts as $row)
                            <li>
                                <span class="strong">{{ $row['name'] }}</span>

                                <strong>@lang('saas::app.super-admin.dashboard.subscriptions-count', ['count' => $row['count']])</strong>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    @if ($failedOperations->isNotEmpty())
        <div class="card mt-16">
            <div class="card-header">
                <div>
                    <h2>@lang('saas::app.super-admin.dashboard.failed-provisioning')</h2>

                    <p>@lang('saas::app.super-admin.dashboard.failed-provisioning-subtitle')</p>
                </div>

                <a href="{{ route('saas.super-admin.provisioning.index') }}">@lang('saas::app.super-admin.dashboard.view-all')</a>
            </div>

            <ul class="checklist">
                @foreach ($failedOperations as $operation)
                    <li>
                        <a href="{{ route('saas.super-admin.tenants.show', $operation->tenant_id) }}">
                            {{ $operation->tenant?->name ?? '#'.$operation->tenant_id }}
                        </a>

                        <span class="badge failed">{{ $operation->step }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mt-16">
        <div class="card-header">
            <h2>@lang('saas::app.super-admin.dashboard.recent-activity')</h2>

            <a href="{{ route('saas.super-admin.audit.index') }}">@lang('saas::app.super-admin.dashboard.view-all')</a>
        </div>

        @if ($recentAudit->isEmpty())
            <div class="empty-block">
                @include('saas::layouts.partials.icon', ['name' => 'audit'])

                <p>@lang('saas::app.common.none')</p>
            </div>
        @else
            <div class="table-wrap" style="border: 0; border-radius: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>@lang('saas::app.super-admin.audit.action')</th>
                            <th>@lang('saas::app.super-admin.audit.tenant')</th>
                            <th>@lang('saas::app.super-admin.audit.actor')</th>
                            <th>@lang('saas::app.super-admin.audit.when')</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($recentAudit as $entry)
                            <tr>
                                <td class="strong">{{ $entry->action }}</td>
                                <td>{{ $entry->tenant?->name ?? trans('saas::app.super-admin.audit.platform') }}</td>
                                <td>{{ $entry->actor_name }}</td>
                                <td class="nowrap">{{ $entry->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
