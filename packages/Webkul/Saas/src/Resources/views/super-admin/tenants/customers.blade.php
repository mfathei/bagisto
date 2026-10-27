@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.tenants.customers-list'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.tenants.index') }}">@lang('saas::app.super-admin.nav.tenants')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.tenants.customers-list')</span>
            </div>

            <h1>@lang('saas::app.super-admin.tenants.customers-list')</h1>

            <p>@lang('saas::app.super-admin.tenants.customers-subtitle')</p>
        </div>
    </div>

    <div class="datagrid">
        <form class="dg-toolbar" method="GET" action="{{ route('saas.super-admin.tenants.customers') }}">
            <div class="dg-toolbar-left">
                <label class="dg-search" for="customer-search">
                    @include('saas::layouts.partials.icon', ['name' => 'search'])

                    <input
                        id="customer-search"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="@lang('saas::app.super-admin.tenants.search-customers')"
                        autocomplete="off"
                    >
                </label>

                <span class="dg-count">
                    @if ($customers->total())
                        @lang('saas::app.super-admin.datagrid.showing', [
                            'from' => $customers->firstItem(),
                            'to' => $customers->lastItem(),
                            'total' => $customers->total(),
                        ])
                    @else
                        @lang('saas::app.super-admin.datagrid.no-records')
                    @endif
                </span>
            </div>

            <div class="dg-toolbar-right">
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
                        <th>@lang('saas::app.super-admin.tenants.customer')</th>
                        <th>@lang('saas::app.super-admin.tenants.domain')</th>
                        <th>@lang('saas::app.super-admin.tenants.group')</th>
                        <th>@lang('saas::app.super-admin.tenants.phone')</th>
                        <th>@lang('saas::app.super-admin.tenants.orders')</th>
                        <th>@lang('saas::app.common.status')</th>
                        <th>@lang('saas::app.super-admin.tenants.joined')</th>
                        <th class="end">@lang('saas::app.super-admin.tenants.customer-id')</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($customers as $customer)
                        @php
                            $hostname = $tenantDirectory[$customer->tenant_id] ?? null;
                        @endphp

                        <tr>
                            <td data-label="@lang('saas::app.super-admin.tenants.customer')">
                                <div class="cell-media">
                                    <span class="avatar avatar-sm">{{ str($customer->first_name ?: $customer->email)->substr(0, 1)->upper() }}</span>

                                    <span>
                                        <span class="cell-title">{{ $customer->first_name }} {{ $customer->last_name }}</span>

                                        <div class="cell-sub">{{ $customer->email }}</div>
                                    </span>
                                </div>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.domain')">
                                @if ($hostname)
                                    <a href="{{ route('saas.super-admin.tenants.show', $customer->tenant_id) }}">{{ $hostname }}</a>
                                @else
                                    {{ trans('saas::app.common.none') }}
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.group')">
                                <span class="badge neutral no-dot">{{ $customer->group?->name ?? trans('saas::app.common.none') }}</span>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.phone')">
                                {{ $customer->phone ?? trans('saas::app.common.none') }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.orders')" class="nowrap">
                                {{ (int) $customer->orders_count }}
                            </td>

                            <td data-label="@lang('saas::app.common.status')">
                                @if ($customer->status)
                                    <span class="badge active">@lang('saas::app.super-admin.tenants.enabled')</span>
                                @else
                                    <span class="badge neutral">@lang('saas::app.super-admin.tenants.disabled')</span>
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.joined')" class="nowrap">
                                {{ $customer->created_at?->format('M d, Y') }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.customer-id')" class="end nowrap">
                                #{{ $customer->getKey() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="dg-empty">
                                    @include('saas::layouts.partials.icon', ['name' => 'customers'])

                                    <p>@lang('saas::app.super-admin.tenants.no-customers')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="dg-footer">
                <span>
                    @lang('saas::app.super-admin.datagrid.page-of', [
                        'page' => $customers->currentPage(),
                        'pages' => $customers->lastPage(),
                    ])
                </span>

                <div class="pagination">{{ $customers->links('saas::layouts.partials.pagination') }}</div>
            </div>
        @endif
    </div>
@endsection
