@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.provisioning.title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.configure.index') }}">@lang('saas::app.super-admin.nav.configuration')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.provisioning.title')</span>
            </div>

            <h1>@lang('saas::app.super-admin.provisioning.title')</h1>

            <p>@lang('saas::app.super-admin.provisioning.subtitle')</p>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.configure.index') }}">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])

                @lang('saas::app.super-admin.nav.configuration')
            </a>
        </div>
    </div>

    <div class="datagrid">
        <form class="dg-toolbar" method="GET" action="{{ route('saas.super-admin.provisioning.index') }}">
            <div class="dg-toolbar-left">
                <span class="dg-count">
                    @if ($operations->total())
                        @lang('saas::app.super-admin.datagrid.showing', [
                            'from' => $operations->firstItem(),
                            'to' => $operations->lastItem(),
                            'total' => $operations->total(),
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
                                <option value="">@lang('saas::app.super-admin.provisioning.all-statuses')</option>

                                @foreach (\Webkul\Saas\Enums\ProvisioningStatus::cases() as $case)
                                    <option value="{{ $case->value }}" @selected(($status ?? '') === $case->value)>
                                        {{ $case->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-actions">
                            <a class="btn small ghost" href="{{ route('saas.super-admin.provisioning.index') }}">
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
                        <th>@lang('saas::app.common.tenant')</th>
                        <th>@lang('saas::app.super-admin.provisioning.type')</th>
                        <th>@lang('saas::app.common.status')</th>
                        <th>@lang('saas::app.super-admin.provisioning.step')</th>
                        <th class="center">@lang('saas::app.super-admin.provisioning.attempts')</th>
                        <th>@lang('saas::app.super-admin.provisioning.started')</th>
                        <th class="end">@lang('saas::app.common.actions')</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($operations as $operation)
                        <tr>
                            <td data-label="@lang('saas::app.common.tenant')">
                                <a class="cell-title" href="{{ route('saas.super-admin.tenants.show', $operation->tenant_id) }}">
                                    {{ $operation->tenant?->name ?? '#'.$operation->tenant_id }}
                                </a>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.provisioning.type')">
                                <span class="badge">{{ $operation->type }}</span>
                            </td>

                            <td data-label="@lang('saas::app.common.status')">
                                <span class="badge {{ $operation->status->value }}">{{ $operation->status->value }}</span>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.provisioning.step')">
                                {{ $operation->step ?: trans('saas::app.common.none') }}

                                @if ($operation->error)
                                    <div class="cell-sub">{{ \Illuminate\Support\Str::limit($operation->error, 180) }}</div>
                                @endif
                            </td>

                            <td class="center" data-label="@lang('saas::app.super-admin.provisioning.attempts')">
                                {{ $operation->attempts }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.provisioning.started')" class="nowrap">
                                {{ $operation->started_at?->format('M d, Y H:i') ?? trans('saas::app.common.none') }}
                            </td>

                            <td class="end" data-label="@lang('saas::app.common.actions')">
                                @if ($operation->status === \Webkul\Saas\Enums\ProvisioningStatus::Failed)
                                    <form method="POST" action="{{ route('saas.super-admin.provisioning.retry', $operation->getKey()) }}" class="inline-form">
                                        @csrf

                                        <button type="submit" class="btn small">
                                            @include('saas::layouts.partials.icon', ['name' => 'refresh'])

                                            @lang('saas::app.super-admin.provisioning.retry')
                                        </button>
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
                                    @include('saas::layouts.partials.icon', ['name' => 'provisioning'])

                                    <p>@lang('saas::app.super-admin.provisioning.empty')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($operations->hasPages())
            <div class="dg-footer">
                <span>
                    @lang('saas::app.super-admin.datagrid.page-of', [
                        'page' => $operations->currentPage(),
                        'pages' => $operations->lastPage(),
                    ])
                </span>

                <div class="pagination">{{ $operations->links('saas::layouts.partials.pagination') }}</div>
            </div>
        @endif
    </div>
@endsection
