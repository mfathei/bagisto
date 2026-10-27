@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.dashboard.title'))

@php
    $currency = config('saas.billing.currency', 'USD');

    $signupsTotal = array_sum(array_column($signupsPerDay, 'value'));

    $signupsMax = max(1, (int) max(array_column($signupsPerDay, 'value') ?: [0]));

    $revenueTotal = array_sum(array_column($revenuePerDay, 'value'));

    $revenueMax = max(1, (int) max(array_column($revenuePerDay, 'value') ?: [0]));
@endphp

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.super-admin.dashboard.greeting', ['name' => auth('super_admin')->user()->name ?? ''])</h1>

            <p>@lang('saas::app.super-admin.dashboard.subtitle')</p>
        </div>

        <form class="range-form" method="GET" action="{{ route('saas.super-admin.dashboard') }}">
            <input type="date" name="from" value="{{ $from->toDateString() }}">

            <input type="date" name="to" value="{{ $to->toDateString() }}">

            <button type="submit" class="btn small">@lang('saas::app.super-admin.dashboard.apply-range')</button>
        </form>
    </div>

    <h2 class="section-title">@lang('saas::app.super-admin.dashboard.overall-details')</h2>

    <div class="grid stats-grid">
        <div class="card stat-card">
            <span class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18"></path>
                    <path d="M5 21V7l7-4 7 4v14"></path>
                    <path d="M9 9h1"></path>
                    <path d="M9 13h1"></path>
                    <path d="M9 17h1"></path>
                    <path d="M14 9h1"></path>
                    <path d="M14 13h1"></path>
                    <path d="M14 17h1"></path>
                </svg>
            </span>

            <div>
                <p class="stat-value">{{ $totalTenants }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.dashboard.total-tenants')</span>

                @if (! is_null($tenantGrowth))
                    <span class="stat-delta {{ $tenantGrowth >= 0 ? 'up' : 'down' }}">{!! $tenantGrowth >= 0 ? '&uarr;' : '&darr;' !!} {{ abs($tenantGrowth) }}%</span>
                @elseif ($totalTenants > 0)
                    <span class="stat-delta new">@lang('saas::app.super-admin.dashboard.new-this-period')</span>
                @endif
            </div>
        </div>

        <div class="card stat-card">
            <span class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"></circle>
                    <path d="M4 21v-1a7 7 0 0 1 14 0v1"></path>
                </svg>
            </span>

            <div>
                <p class="stat-value">{{ $activeTenants }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.dashboard.active-tenants')</span>
            </div>
        </div>

        <div class="card stat-card">
            <span class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 1 1-3-6.7"></path>
                    <path d="M21 3v6h-6"></path>
                </svg>
            </span>

            <div>
                <p class="stat-value">{{ $activeSubscriptions }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.dashboard.active-subscriptions')</span>
            </div>
        </div>

        <div class="card stat-card">
            <span class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 6v6l4 2"></path>
                </svg>
            </span>

            <div>
                <p class="stat-value">{{ $trialingSubscriptions }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.dashboard.trial-subscriptions')</span>
            </div>
        </div>

        <div class="card stat-card">
            <span class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20"></path>
                    <path d="M17 5.5c0-1.4-2.2-2.5-5-2.5s-5 1.1-5 2.5 2.2 2.5 5 2.5 5 1.1 5 2.5-2.2 2.5-5 2.5-5-1.1-5-2.5"></path>
                </svg>
            </span>

            <div>
                <p class="stat-value">{{ $currency }} {{ number_format((float) $totalRevenue, 2) }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.dashboard.total-revenue')</span>

                @if (! is_null($revenueGrowth))
                    <span class="stat-delta {{ $revenueGrowth >= 0 ? 'up' : 'down' }}">{!! $revenueGrowth >= 0 ? '&uarr;' : '&darr;' !!} {{ abs($revenueGrowth) }}%</span>
                @elseif ($totalRevenue > 0)
                    <span class="stat-delta new">@lang('saas::app.super-admin.dashboard.new-this-period')</span>
                @endif
            </div>
        </div>

        <div class="card stat-card">
            <span class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                    <path d="M10.3 3.6 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.6a2 2 0 0 0-3.4 0Z"></path>
                </svg>
            </span>

            <div>
                <p class="stat-value">{{ $currency }} {{ number_format((float) $dueAmount, 2) }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.dashboard.due-amount')</span>
            </div>
        </div>
    </div>

    <div class="dashboard-cols">
        <div class="dashboard-main">
            <div class="card">
                <div class="card-head">
                    <h2>@lang('saas::app.super-admin.dashboard.recent-tenants')</h2>

                    <a href="{{ route('saas.super-admin.tenants.index') }}">@lang('saas::app.super-admin.dashboard.view-all')</a>
                </div>

                @if ($recentTenants->isEmpty())
                    <p class="muted">@lang('saas::app.common.none')</p>
                @else
                    <ul class="row-list">
                        @foreach ($recentTenants as $tenant)
                            @php
                                $domain = $tenant->domains->firstWhere('is_primary', true) ?? $tenant->domains->firstWhere('status', 'verified');
                            @endphp

                            <li class="row-item">
                                <div class="row-main">
                                    <a class="row-title" href="{{ route('saas.super-admin.tenants.show', $tenant->id) }}">{{ $tenant->name }}</a>

                                    <p class="row-sub">{{ $domain?->hostname ?? $tenant->slug }}</p>
                                </div>

                                <div class="row-side">
                                    <span class="badge {{ $tenant->status->value }}">{{ $tenant->status->label() }}</span>

                                    <p class="row-sub">{{ $tenant->created_at?->format('d M Y, H:i') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card">
                <div class="card-head">
                    <h2>@lang('saas::app.super-admin.dashboard.recent-invoices')</h2>

                    <a href="{{ route('saas.super-admin.billing.index') }}">@lang('saas::app.super-admin.dashboard.view-all')</a>
                </div>

                @if ($recentInvoices->isEmpty())
                    <p class="muted">@lang('saas::app.common.none')</p>
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
                <div class="card-head">
                    <h2>@lang('saas::app.super-admin.dashboard.expiring-soon')</h2>

                    <a href="{{ route('saas.super-admin.subscriptions.index') }}">@lang('saas::app.super-admin.dashboard.view-all')</a>
                </div>

                @if ($expiringSoon->isEmpty())
                    <p class="muted">@lang('saas::app.super-admin.dashboard.no-expiring', ['days' => 14])</p>
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

                                    <p class="row-sub">{{ $endsAt?->format('d M Y') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="dashboard-side">
            <div class="card">
                <div class="chart-head">
                    <h3>@lang('saas::app.super-admin.dashboard.signups-chart')</h3>

                    <span class="chart-total">
                        {{ $signupsTotal }}

                        <span>{{ $from->format('d M') }} &ndash; {{ $to->format('d M') }}</span>
                    </span>
                </div>

                <div class="bars">
                    @foreach ($signupsPerDay as $bucket)
                        <div class="bar-col">
                            <span
                                class="bar {{ $bucket['value'] > 0 ? '' : 'zero' }}"
                                style="height: {{ $bucket['value'] > 0 ? max(3, (int) round($bucket['value'] / $signupsMax * 100)) : 3 }}%"
                                title="{{ $bucket['label'] }}: {{ $bucket['value'] }}"
                            ></span>
                        </div>
                    @endforeach
                </div>

                <div class="bar-labels">
                    @foreach ($signupsPerDay as $bucket)
                        <span>{{ $bucket['label'] }}</span>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="chart-head">
                    <h3>@lang('saas::app.super-admin.dashboard.revenue-chart')</h3>

                    <span class="chart-total">
                        <strong>{{ $currency }} {{ number_format((float) $revenueTotal, 2) }}</strong>

                        <span>{{ $from->format('d M') }} &ndash; {{ $to->format('d M') }}</span>
                    </span>
                </div>

                <div class="bars">
                    @foreach ($revenuePerDay as $bucket)
                        <div class="bar-col">
                            <span
                                class="bar {{ $bucket['value'] > 0 ? '' : 'zero' }}"
                                style="height: {{ $bucket['value'] > 0 ? max(3, (int) round($bucket['value'] / $revenueMax * 100)) : 3 }}%"
                                title="{{ $bucket['label'] }}: {{ $currency }} {{ number_format((float) $bucket['value'], 2) }}"
                            ></span>
                        </div>
                    @endforeach
                </div>

                <div class="bar-labels">
                    @foreach ($revenuePerDay as $bucket)
                        <span>{{ $bucket['label'] }}</span>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <h2>@lang('saas::app.super-admin.dashboard.plan-distribution')</h2>

                @if ($planCounts->isEmpty())
                    <p class="muted">@lang('saas::app.common.none')</p>
                @else
                    <ul class="checklist">
                        @foreach ($planCounts as $row)
                            <li>
                                <span>{{ $row['name'] }}</span>

                                <strong>@lang('saas::app.super-admin.dashboard.subscriptions-count', ['count' => $row['count']])</strong>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    @if ($failedOperations->isNotEmpty())
        <div class="card" style="margin-top: 16px;">
            <h2>@lang('saas::app.super-admin.dashboard.failed-provisioning')</h2>

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

    <div class="card" style="margin-top: 16px;">
        <h2>@lang('saas::app.super-admin.dashboard.recent-activity')</h2>

        @if ($recentAudit->isEmpty())
            <p class="muted">@lang('saas::app.common.none')</p>
        @else
            <div class="table-wrap">
                <table>
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
                                <td>{{ $entry->action }}</td>
                                <td>{{ $entry->tenant?->name ?? trans('saas::app.super-admin.audit.platform') }}</td>
                                <td>{{ $entry->actor_name }}</td>
                                <td>{{ $entry->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
