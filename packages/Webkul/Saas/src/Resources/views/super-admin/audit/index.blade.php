@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.audit.title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.configure.index') }}">@lang('saas::app.super-admin.nav.configuration')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.audit.title')</span>
            </div>

            <h1>@lang('saas::app.super-admin.audit.title')</h1>

            <p>@lang('saas::app.super-admin.audit.subtitle')</p>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.configure.index') }}">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])

                @lang('saas::app.super-admin.nav.configuration')
            </a>
        </div>
    </div>

    <div class="datagrid">
        <form class="dg-toolbar" method="GET" action="{{ route('saas.super-admin.audit.index') }}">
            <div class="dg-toolbar-left">
                <label class="dg-search">
                    @include('saas::layouts.partials.icon', ['name' => 'search'])

                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="@lang('saas::app.super-admin.audit.search')"
                        autocomplete="off"
                    >
                </label>

                <span class="dg-count">
                    @if ($logs->total())
                        @lang('saas::app.super-admin.datagrid.showing', [
                            'from' => $logs->firstItem(),
                            'to' => $logs->lastItem(),
                            'total' => $logs->total(),
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
                            <label for="action">@lang('saas::app.super-admin.audit.action')</label>

                            <select id="action" name="action">
                                <option value="">@lang('saas::app.super-admin.audit.all-actions')</option>

                                @foreach (\Webkul\Saas\Enums\AuditAction::cases() as $action)
                                    <option value="{{ $action->value }}" @selected(($filters['action'] ?? '') === $action->value)>
                                        {{ $action->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="tenant_id">@lang('saas::app.super-admin.billing.tenant-id')</label>

                            <input id="tenant_id" type="number" name="tenant_id" min="1" value="{{ $filters['tenant_id'] ?? '' }}">
                        </div>

                        <div class="form-actions">
                            <a class="btn small ghost" href="{{ route('saas.super-admin.audit.index') }}">
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
                        <th>@lang('saas::app.super-admin.audit.when')</th>
                        <th>@lang('saas::app.super-admin.audit.action')</th>
                        <th>@lang('saas::app.super-admin.audit.actor')</th>
                        <th>@lang('saas::app.common.tenant')</th>
                        <th>@lang('saas::app.super-admin.audit.description')</th>
                        <th>@lang('saas::app.super-admin.audit.ip')</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td data-label="@lang('saas::app.super-admin.audit.when')" class="nowrap">
                                {{ $log->created_at?->format('M d, Y H:i') }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.audit.action')">
                                <span class="badge">{{ $log->action }}</span>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.audit.actor')">
                                {{ $log->actor_name ?: $log->actor_type }}

                                @if ($log->actor_id)
                                    <span class="muted">#{{ $log->actor_id }}</span>
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.common.tenant')">
                                @if ($log->tenant_id)
                                    <a href="{{ route('saas.super-admin.tenants.show', $log->tenant_id) }}">
                                        {{ $log->tenant?->name ?? '#'.$log->tenant_id }}
                                    </a>
                                @else
                                    <span class="muted">@lang('saas::app.super-admin.audit.platform')</span>
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.super-admin.audit.description')">
                                {{ $log->description }}

                                @if (! empty($log->metadata))
                                    <details style="margin-top: 4px;">
                                        <summary class="muted">@lang('saas::app.super-admin.audit.metadata')</summary>

                                        <pre dir="ltr" style="white-space: pre-wrap; font-size: 12px;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.super-admin.audit.ip')" dir="ltr">
                                {{ $log->ip_address ?: trans('saas::app.common.none') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="dg-empty">
                                    @include('saas::layouts.partials.icon', ['name' => 'audit'])

                                    <p>@lang('saas::app.super-admin.audit.empty')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="dg-footer">
                <span>
                    @lang('saas::app.super-admin.datagrid.page-of', [
                        'page' => $logs->currentPage(),
                        'pages' => $logs->lastPage(),
                    ])
                </span>

                <div class="pagination">{{ $logs->links('saas::layouts.partials.pagination') }}</div>
            </div>
        @endif
    </div>
@endsection
