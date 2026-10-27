@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.tenants.orders-list'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.tenants.index') }}">@lang('saas::app.super-admin.nav.tenants')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.tenants.orders-list')</span>
            </div>

            <h1>@lang('saas::app.super-admin.tenants.orders-list')</h1>

            <p>@lang('saas::app.super-admin.tenants.orders-subtitle')</p>
        </div>
    </div>

    <div class="datagrid">
        <form class="dg-toolbar" method="GET" action="{{ route('saas.super-admin.tenants.orders') }}">
            <div class="dg-toolbar-left">
                <label class="dg-search" for="order-search">
                    @include('saas::layouts.partials.icon', ['name' => 'search'])

                    <input
                        id="order-search"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="@lang('saas::app.super-admin.tenants.search-orders')"
                        autocomplete="off"
                    >
                </label>

                <span class="dg-count">
                    @if ($orders->total())
                        @lang('saas::app.super-admin.datagrid.showing', [
                            'from' => $orders->firstItem(),
                            'to' => $orders->lastItem(),
                            'total' => $orders->total(),
                        ])
                    @else
                        @lang('saas::app.super-admin.datagrid.no-records')
                    @endif
                </span>
            </div>

            <div class="dg-toolbar-right">
                <select name="status" class="dg-select" aria-label="@lang('saas::app.common.status')" data-auto-submit>
                    <option value="">@lang('saas::app.super-admin.tenants.all-statuses')</option>

                    @foreach (['pending', 'processing', 'completed', 'canceled', 'closed', 'fraud'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>

                <select name="tenant_id" class="dg-select" aria-label="@lang('saas::app.common.tenant')" data-auto-submit>
                    <option value="">@lang('saas::app.super-admin.tenants.all-tenants')</option>

                    @foreach ($tenantOptions as $option)
                        <option value="{{ $option->getKey() }}" @selected((string) $tenantId === (string) $option->getKey())>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn small">@lang('saas::app.super-admin.datagrid.apply')</button>
            </div>
        </form>

        <div style="overflow-x: auto;">
            <table class="dg-table">
                <thead>
                    <tr>
                        <th>@lang('saas::app.super-admin.tenants.order')</th>
                        <th>@lang('saas::app.super-admin.tenants.domain')</th>
                        <th>@lang('saas::app.super-admin.tenants.customer')</th>
                        <th>@lang('saas::app.super-admin.tenants.grand-total')</th>
                        <th>@lang('saas::app.super-admin.tenants.payment-method')</th>
                        <th>@lang('saas::app.super-admin.tenants.channel')</th>
                        <th>@lang('saas::app.super-admin.tenants.items')</th>
                        <th>@lang('saas::app.common.status')</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $hostname = $tenantDirectory[$order->tenant_id] ?? null;

                            $address = $order->billing_address;
                        @endphp

                        <tr>
                            <td data-label="@lang('saas::app.super-admin.tenants.order')">
                                <span class="cell-title">#{{ $order->increment_id }}</span>

                                <div class="cell-sub">{{ $order->created_at?->format('M d, Y H:i') }}</div>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.domain')">
                                @if ($hostname)
                                    <a href="{{ route('saas.super-admin.tenants.show', $order->tenant_id) }}">{{ $hostname }}</a>
                                @else
                                    {{ trans('saas::app.common.none') }}
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.customer')">
                                <span class="cell-title">{{ $order->customer_first_name }} {{ $order->customer_last_name }}</span>

                                <div class="cell-sub">{{ $order->customer_email }}</div>

                                @if ($address)
                                    <div class="cell-sub">{{ $address->city }}{{ $address->country ? ', '.$address->country : '' }}</div>
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.grand-total')" class="nowrap">
                                <span class="cell-title">
                                    {{ number_format((float) $order->grand_total, 2) }} {{ $order->order_currency_code }}
                                </span>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.payment-method')">
                                {{ $order->payment?->method_title ?? $order->payment?->method ?? trans('saas::app.common.none') }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.channel')">
                                {{ $order->channel_name ?? trans('saas::app.common.none') }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.items')" class="nowrap">
                                {{ (int) $order->items_count }}
                            </td>

                            <td data-label="@lang('saas::app.common.status')">
                                <span class="badge {{ $order->status }}">{{ $order->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="dg-empty">
                                    @include('saas::layouts.partials.icon', ['name' => 'orders'])

                                    <p>@lang('saas::app.super-admin.tenants.no-orders')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="dg-footer">
                <span>
                    @lang('saas::app.super-admin.datagrid.page-of', [
                        'page' => $orders->currentPage(),
                        'pages' => $orders->lastPage(),
                    ])
                </span>

                <div class="pagination">{{ $orders->links('saas::layouts.partials.pagination') }}</div>
            </div>
        @endif
    </div>
@endsection
