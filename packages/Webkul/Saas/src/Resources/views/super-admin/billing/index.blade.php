@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.billing.title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.nav.subscription-invoices')</span>
            </div>

            <h1>@lang('saas::app.super-admin.billing.title')</h1>

            <p>@lang('saas::app.super-admin.billing.subtitle', ['provider' => $provider->name()])</p>
        </div>

        <div class="page-actions">
            <form method="POST" action="{{ route('saas.super-admin.billing.mark_overdue') }}" class="inline-form">
                @csrf

                <button type="submit" class="btn secondary">@lang('saas::app.super-admin.billing.mark-overdue')</button>
            </form>
        </div>
    </div>

    <div class="datagrid">
        <form class="dg-toolbar" method="GET" action="{{ route('saas.super-admin.billing.index') }}">
            <div class="dg-toolbar-left">
                <span class="dg-count">
                    @if ($invoices->total())
                        @lang('saas::app.super-admin.datagrid.showing', [
                            'from' => $invoices->firstItem(),
                            'to' => $invoices->lastItem(),
                            'total' => $invoices->total(),
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
                                <option value="">@lang('saas::app.super-admin.billing.all-statuses')</option>

                                @foreach (\Webkul\Saas\Enums\BillingStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                                        {{ $status->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="tenant_id">@lang('saas::app.super-admin.billing.tenant-id')</label>

                            <input id="tenant_id" type="number" name="tenant_id" min="1" value="{{ $filters['tenant_id'] ?? '' }}">
                        </div>

                        <div class="form-actions">
                            <a class="btn small ghost" href="{{ route('saas.super-admin.billing.index') }}">@lang('saas::app.common.reset')</a>

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
                        <th>@lang('saas::app.common.number')</th>
                        <th>@lang('saas::app.common.tenant')</th>
                        <th>@lang('saas::app.common.status')</th>
                        <th>@lang('saas::app.common.total')</th>
                        <th>@lang('saas::app.common.balance')</th>
                        <th>@lang('saas::app.super-admin.billing.due')</th>
                        <th>@lang('saas::app.common.created')</th>
                        <th class="end">@lang('saas::app.common.actions')</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td data-label="@lang('saas::app.common.number')">
                                <a class="cell-title" href="{{ route('saas.super-admin.billing.show', $invoice->getKey()) }}">
                                    {{ $invoice->number }}
                                </a>
                            </td>

                            <td data-label="@lang('saas::app.common.tenant')">
                                @if ($invoice->tenant)
                                    <a href="{{ route('saas.super-admin.tenants.show', $invoice->tenant->getKey()) }}">
                                        {{ $invoice->tenant->name }}
                                    </a>

                                    <div class="cell-sub">{{ $invoice->tenant->email }}</div>
                                @else
                                    #{{ $invoice->tenant_id }}
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.common.status')">
                                <span class="badge {{ $invoice->status->value }}">{{ $invoice->status->value }}</span>
                            </td>

                            <td data-label="@lang('saas::app.common.total')" class="nowrap">
                                {{ number_format((float) $invoice->total, 2) }} {{ $invoice->currency }}
                            </td>

                            <td data-label="@lang('saas::app.common.balance')" class="nowrap">
                                {{ number_format($invoice->balance(), 2) }} {{ $invoice->currency }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.billing.due')" class="nowrap">
                                {{ $invoice->due_at?->format('M d, Y') ?? trans('saas::app.common.none') }}
                            </td>

                            <td data-label="@lang('saas::app.common.created')" class="nowrap">
                                {{ $invoice->created_at?->format('M d, Y') }}
                            </td>

                            <td class="end" data-label="@lang('saas::app.common.actions')">
                                <a
                                    class="icon-btn bordered"
                                    href="{{ route('saas.super-admin.billing.show', $invoice->getKey()) }}"
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
                                    @include('saas::layouts.partials.icon', ['name' => 'invoices'])

                                    <p>@lang('saas::app.super-admin.billing.empty')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="dg-footer">
                <span>
                    @lang('saas::app.super-admin.datagrid.page-of', [
                        'page' => $invoices->currentPage(),
                        'pages' => $invoices->lastPage(),
                    ])
                </span>

                <div class="pagination">{{ $invoices->links('saas::layouts.partials.pagination') }}</div>
            </div>
        @endif
    </div>
@endsection
