@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.subscriptions.title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.nav.purchased-plans')</span>
            </div>

            <h1>@lang('saas::app.super-admin.subscriptions.title')</h1>

            <p>@lang('saas::app.super-admin.subscriptions.subtitle')</p>
        </div>
    </div>

    <div class="datagrid">
        <form class="dg-toolbar" method="GET" action="{{ route('saas.super-admin.subscriptions.index') }}">
            <div class="dg-toolbar-left">
                <span class="dg-count">
                    @if ($subscriptions->total())
                        @lang('saas::app.super-admin.datagrid.showing', [
                            'from' => $subscriptions->firstItem(),
                            'to' => $subscriptions->lastItem(),
                            'total' => $subscriptions->total(),
                        ])
                    @else
                        @lang('saas::app.super-admin.datagrid.no-records')
                    @endif
                </span>
            </div>

            <div class="dg-toolbar-right">
                <details class="filter-popover">
                    <summary class="dg-filter">
                        @include('saas::layouts.partials.icon', ['name' => 'filter'])

                        @lang('saas::app.super-admin.datagrid.filter')
                    </summary>

                    <div class="filter-panel">
                        <div class="field">
                            <label for="status">@lang('saas::app.common.status')</label>

                            <select id="status" name="status">
                                <option value="">@lang('saas::app.super-admin.subscriptions.all-statuses')</option>

                                @foreach (\Webkul\Saas\Enums\SubscriptionStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                                        {{ $status->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="plan_id">@lang('saas::app.common.plan')</label>

                            <select id="plan_id" name="plan_id">
                                <option value="">@lang('saas::app.super-admin.tenants.all-plans')</option>

                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->getKey() }}" @selected((string) ($filters['plan_id'] ?? '') === (string) $plan->getKey())>
                                        {{ $plan->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-actions">
                            <a class="btn small ghost" href="{{ route('saas.super-admin.subscriptions.index') }}">@lang('saas::app.common.reset')</a>

                            <button type="submit" class="btn small">@lang('saas::app.super-admin.datagrid.apply')</button>
                        </div>
                    </div>
                </details>
            </div>
        </form>

        <div style="overflow-x: auto;">
            <table class="dg-table">
                <thead>
                    <tr>
                        <th>@lang('saas::app.super-admin.subscriptions.id')</th>
                        <th>@lang('saas::app.super-admin.subscriptions.company-name')</th>
                        <th>@lang('saas::app.common.plan')</th>
                        <th>@lang('saas::app.super-admin.subscriptions.amount')</th>
                        <th>@lang('saas::app.super-admin.subscriptions.period-unit')</th>
                        <th>@lang('saas::app.super-admin.subscriptions.profile-state')</th>
                        <th>@lang('saas::app.common.created')</th>
                        <th class="end">@lang('saas::app.common.actions')</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($subscriptions as $subscription)
                        <tr>
                            <td data-label="@lang('saas::app.super-admin.subscriptions.id')" class="nowrap">
                                #{{ $subscription->getKey() }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.subscriptions.company-name')">
                                @if ($subscription->tenant)
                                    <a class="cell-title" href="{{ route('saas.super-admin.tenants.show', $subscription->tenant->getKey()) }}">
                                        {{ $subscription->tenant->name }}
                                    </a>

                                    <div class="cell-sub">{{ $subscription->tenant->email }}</div>
                                @else
                                    #{{ $subscription->tenant_id }}
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.common.plan')">
                                @if ($subscription->plan)
                                    <a href="{{ route('saas.super-admin.plans.show', $subscription->plan->getKey()) }}">
                                        {{ $subscription->plan->name }}
                                    </a>
                                @else
                                    {{ trans('saas::app.common.none') }}
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.super-admin.subscriptions.amount')" class="nowrap">
                                {{ $subscription->plan?->price ?? '0.00' }}
                                {{ $subscription->plan?->currency }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.subscriptions.period-unit')">
                                {{ $subscription->plan?->billing_interval ?? trans('saas::app.common.none') }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.subscriptions.profile-state')">
                                <span class="badge {{ $subscription->status->value }}">{{ $subscription->status->value }}</span>

                                @if ($subscription->tenant)
                                    <div class="cell-sub">
                                        <span class="badge {{ $subscription->tenant->status->value }} no-dot">{{ $subscription->tenant->status->label() }}</span>
                                    </div>
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.common.created')" class="nowrap">
                                {{ $subscription->created_at?->format('M d, Y') }}
                            </td>

                            <td class="end" data-label="@lang('saas::app.common.actions')">
                                <a
                                    class="icon-btn bordered"
                                    href="{{ route('saas.super-admin.subscriptions.show', $subscription->getKey()) }}"
                                    title="@lang('saas::app.common.view')"
                                >
                                    @include('saas::layouts.partials.icon', ['name' => 'view'])
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="dg-empty">
                                    @include('saas::layouts.partials.icon', ['name' => 'subscriptions'])

                                    <p>@lang('saas::app.super-admin.subscriptions.empty')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($subscriptions->hasPages())
            <div class="dg-footer">
                <span>
                    @lang('saas::app.super-admin.datagrid.page-of', [
                        'page' => $subscriptions->currentPage(),
                        'pages' => $subscriptions->lastPage(),
                    ])
                </span>

                <div class="pagination">{{ $subscriptions->links('saas::layouts.partials.pagination') }}</div>
            </div>
        @endif
    </div>
@endsection
