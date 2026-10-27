@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.domains.title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.configure.index') }}">@lang('saas::app.super-admin.nav.configuration')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.domains.title')</span>
            </div>

            <h1>@lang('saas::app.super-admin.domains.title')</h1>

            <p>@lang('saas::app.super-admin.domains.subtitle')</p>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.configure.index') }}">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])

                @lang('saas::app.super-admin.nav.configuration')
            </a>
        </div>
    </div>

    <div class="datagrid">
        <form class="dg-toolbar" method="GET" action="{{ route('saas.super-admin.domains.index') }}">
            <div class="dg-toolbar-left">
                <label class="dg-search">
                    @include('saas::layouts.partials.icon', ['name' => 'search'])

                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="@lang('saas::app.super-admin.domains.hostname')"
                        autocomplete="off"
                    >
                </label>

                <span class="dg-count">
                    @if ($domains->total())
                        @lang('saas::app.super-admin.datagrid.showing', [
                            'from' => $domains->firstItem(),
                            'to' => $domains->lastItem(),
                            'total' => $domains->total(),
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
                                <option value="">@lang('saas::app.super-admin.domains.all-statuses')</option>

                                @foreach (\Webkul\Saas\Enums\DomainStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                                        {{ $status->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="type">@lang('saas::app.super-admin.domains.type')</label>

                            <select id="type" name="type">
                                <option value="">@lang('saas::app.super-admin.domains.all-types')</option>

                                @foreach (\Webkul\Saas\Enums\DomainType::cases() as $type)
                                    <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>
                                        {{ $type->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-actions">
                            <a class="btn small ghost" href="{{ route('saas.super-admin.domains.index') }}">
                                @lang('saas::app.common.reset')
                            </a>

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
                        <th>@lang('saas::app.super-admin.domains.hostname')</th>
                        <th>@lang('saas::app.common.tenant')</th>
                        <th>@lang('saas::app.super-admin.domains.type')</th>
                        <th>@lang('saas::app.common.status')</th>
                        <th>@lang('saas::app.super-admin.domains.ssl')</th>
                        <th>@lang('saas::app.super-admin.domains.last-checked')</th>
                        <th class="end">@lang('saas::app.common.actions')</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($domains as $domain)
                        <tr>
                            <td data-label="@lang('saas::app.super-admin.domains.hostname')">
                                <span class="cell-title" dir="ltr">{{ $domain->hostname }}</span>

                                @if ($domain->is_primary)
                                    <span class="badge active">@lang('saas::app.super-admin.domains.primary')</span>
                                @endif

                                @if ($domain->failure_reason)
                                    <div class="cell-sub">{{ $domain->failure_reason }}</div>
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.common.tenant')">
                                <a href="{{ route('saas.super-admin.tenants.show', $domain->tenant_id) }}">
                                    {{ $domain->tenant?->name ?? '#'.$domain->tenant_id }}
                                </a>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.domains.type')">
                                <span class="badge">{{ $domain->type->value }}</span>
                            </td>

                            <td data-label="@lang('saas::app.common.status')">
                                <span class="badge {{ $domain->status->value }}">{{ $domain->status->value }}</span>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.domains.ssl')">
                                <span class="badge">{{ $domain->ssl_status->value }}</span>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.domains.last-checked')" class="nowrap">
                                {{ $domain->last_checked_at?->format('M d, Y H:i') ?? trans('saas::app.common.none') }}
                            </td>

                            <td class="end" data-label="@lang('saas::app.common.actions')">
                                @if ($domain->status !== \Webkul\Saas\Enums\DomainStatus::Verified)
                                    <form method="POST" action="{{ route('saas.super-admin.domains.verify', $domain->getKey()) }}" class="inline-form">
                                        @csrf

                                        <button type="submit" class="btn small">@lang('saas::app.super-admin.domains.verify')</button>
                                    </form>
                                @elseif (! $domain->is_primary)
                                    <form method="POST" action="{{ route('saas.super-admin.domains.primary', $domain->getKey()) }}" class="inline-form">
                                        @csrf

                                        <button type="submit" class="btn small secondary">@lang('saas::app.super-admin.domains.make-primary')</button>
                                    </form>
                                @else
                                    <span class="muted">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="dg-empty">
                                    @include('saas::layouts.partials.icon', ['name' => 'domains'])

                                    <p>@lang('saas::app.super-admin.domains.empty')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($domains->hasPages())
            <div class="dg-footer">
                <span>
                    @lang('saas::app.super-admin.datagrid.page-of', [
                        'page' => $domains->currentPage(),
                        'pages' => $domains->lastPage(),
                    ])
                </span>

                <div class="pagination">{{ $domains->links('saas::layouts.partials.pagination') }}</div>
            </div>
        @endif
    </div>
@endsection
