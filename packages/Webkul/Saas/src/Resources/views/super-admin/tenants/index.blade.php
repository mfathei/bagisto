@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.tenants.title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.nav.tenants-list')</span>
            </div>

            <h1>@lang('saas::app.super-admin.tenants.title')</h1>

            <p>@lang('saas::app.super-admin.tenants.subtitle')</p>
        </div>

        <div class="page-actions">
            <a class="btn" href="{{ route('saas.super-admin.tenants.create') }}">
                @include('saas::layouts.partials.icon', ['name' => 'plus'])

                @lang('saas::app.super-admin.tenants.register')
            </a>
        </div>
    </div>

    <div class="datagrid">
        <form class="dg-toolbar" method="GET" action="{{ route('saas.super-admin.tenants.index') }}" id="tenants-filter-form">
            <div class="dg-toolbar-left">
                <label class="dg-search" for="tenant-search">
                    @include('saas::layouts.partials.icon', ['name' => 'search'])

                    <input
                        id="tenant-search"
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="@lang('saas::app.super-admin.tenants.search-placeholder')"
                        autocomplete="off"
                    >
                </label>

                <span class="dg-count">
                    @if ($tenants->total())
                        @lang('saas::app.super-admin.datagrid.showing', [
                            'from' => $tenants->firstItem(),
                            'to' => $tenants->lastItem(),
                            'total' => $tenants->total(),
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
                                <option value="">@lang('saas::app.super-admin.tenants.all-statuses')</option>

                                @foreach (\Webkul\Saas\Enums\TenantStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                                        {{ $status->label() }}
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

                        <div class="grid cols-2">
                            <div class="field">
                                <label for="from">@lang('saas::app.super-admin.datagrid.from')</label>

                                <input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                            </div>

                            <div class="field">
                                <label for="to">@lang('saas::app.super-admin.datagrid.to')</label>

                                <input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                            </div>
                        </div>

                        <div class="form-actions">
                            <a class="btn small ghost" href="{{ route('saas.super-admin.tenants.index') }}">@lang('saas::app.common.reset')</a>

                            <button type="submit" class="btn small">@lang('saas::app.super-admin.datagrid.apply')</button>
                        </div>
                    </div>
                </details>

                <label class="dg-per-page">
                    <span>@lang('saas::app.super-admin.datagrid.per-page')</span>

                    <select name="per_page" class="dg-select" data-auto-submit>
                        @foreach ([10, 20, 50, 100] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </form>

        <div style="overflow-x: auto;">
            <table class="dg-table">
                <thead>
                    <tr>
                        <th class="dg-check">
                            <input type="checkbox" class="dg-check-all" aria-label="@lang('saas::app.super-admin.datagrid.select-all')">
                        </th>
                        <th>@lang('saas::app.super-admin.tenants.name')</th>
                        <th>@lang('saas::app.super-admin.tenants.organization')</th>
                        <th>@lang('saas::app.super-admin.tenants.plan-next-due')</th>
                        <th>@lang('saas::app.super-admin.tenants.status-service')</th>
                        <th class="end">@lang('saas::app.common.actions')</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tenants as $tenant)
                        @php
                            $domain = $tenant->domains->firstWhere('is_primary', true) ?? $tenant->domains->firstWhere('status', 'verified');

                            $hostname = $domain?->hostname ?? $tenant->slug;

                            $nextDue = $tenant->currentSubscription?->current_period_end;
                        @endphp

                        <tr>
                            <td class="dg-check">
                                <input type="checkbox" class="dg-check-row" value="{{ $tenant->getKey() }}" aria-label="{{ $tenant->name }}">
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.name')">
                                <a class="cell-title" href="{{ route('saas.super-admin.tenants.show', $tenant->getKey()) }}">{{ $tenant->name }}</a>

                                <div class="cell-sub">{{ $tenant->slug }} &middot; #{{ $tenant->getKey() }}</div>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.organization')">
                                <div class="cell-title">{{ $tenant->legal_name ?: $tenant->name }}</div>

                                <div class="cell-sub">{{ $hostname }}</div>

                                <div class="cell-sub">{{ $tenant->email ?? trans('saas::app.common.none') }}</div>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.plan-next-due')">
                                <div class="cell-title">{{ $tenant->plan?->name ?? trans('saas::app.common.none') }}</div>

                                <div class="cell-sub">
                                    @lang('saas::app.super-admin.tenants.next-due'):
                                    {{ $nextDue?->format('M d, Y') ?? trans('saas::app.common.none') }}
                                </div>

                                <div class="cell-sub">
                                    @lang('saas::app.super-admin.tenants.joined'):
                                    {{ $tenant->created_at?->format('M d, Y') }}
                                </div>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.status-service')">
                                <span class="badge {{ $tenant->status->value }}">{{ $tenant->status->label() }}</span>

                                <div class="cell-sub">
                                    @if ($tenant->storefrontUrl())
                                        <a href="{{ $tenant->storefrontUrl() }}" target="_blank" rel="noopener">
                                            @lang('saas::app.super-admin.tenants.visit-store')
                                        </a>
                                    @else
                                        @lang('saas::app.super-admin.tenants.not-configured')
                                    @endif
                                </div>
                            </td>

                            <td class="end" data-label="@lang('saas::app.common.actions')">
                                <div class="actions" style="justify-content: flex-end;">
                                    <a
                                        class="icon-btn bordered"
                                        href="{{ route('saas.super-admin.tenants.show', $tenant->getKey()) }}"
                                        title="@lang('saas::app.common.view')"
                                    >
                                        @include('saas::layouts.partials.icon', ['name' => 'view'])
                                    </a>

                                    <a
                                        class="icon-btn bordered"
                                        href="{{ route('saas.super-admin.tenants.edit', $tenant->getKey()) }}"
                                        title="@lang('saas::app.common.edit')"
                                    >
                                        @include('saas::layouts.partials.icon', ['name' => 'edit'])
                                    </a>

                                    @if ($tenant->adminPanelUrl())
                                        <a
                                            class="icon-btn bordered"
                                            href="{{ $tenant->adminPanelUrl() }}"
                                            target="_blank"
                                            rel="noopener"
                                            title="@lang('saas::app.super-admin.tenants.login-as-tenant')"
                                        >
                                            @include('saas::layouts.partials.icon', ['name' => 'external'])
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="dg-empty">
                                    @include('saas::layouts.partials.icon', ['name' => 'tenants'])

                                    <p>@lang('saas::app.super-admin.tenants.empty')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tenants->hasPages())
            <div class="dg-footer">
                <span>
                    @lang('saas::app.super-admin.datagrid.page-of', [
                        'page' => $tenants->currentPage(),
                        'pages' => $tenants->lastPage(),
                    ])
                </span>

                <div class="pagination">{{ $tenants->links('saas::layouts.partials.pagination') }}</div>
            </div>
        @endif
    </div>
@endsection
