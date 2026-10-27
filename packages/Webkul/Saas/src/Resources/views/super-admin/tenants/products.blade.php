@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.tenants.products-list'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.tenants.index') }}">@lang('saas::app.super-admin.nav.tenants')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.tenants.products-list')</span>
            </div>

            <h1>@lang('saas::app.super-admin.tenants.products-list')</h1>

            <p>@lang('saas::app.super-admin.tenants.products-subtitle')</p>
        </div>
    </div>

    <div class="datagrid">
        <form class="dg-toolbar" method="GET" action="{{ route('saas.super-admin.tenants.products') }}">
            <div class="dg-toolbar-left">
                <label class="dg-search" for="product-search">
                    @include('saas::layouts.partials.icon', ['name' => 'search'])

                    <input
                        id="product-search"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="@lang('saas::app.super-admin.tenants.search-products')"
                        autocomplete="off"
                    >
                </label>

                <span class="dg-count">
                    @if ($products->total())
                        @lang('saas::app.super-admin.datagrid.showing', [
                            'from' => $products->firstItem(),
                            'to' => $products->lastItem(),
                            'total' => $products->total(),
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
                        <th>@lang('saas::app.super-admin.tenants.product')</th>
                        <th>@lang('saas::app.super-admin.tenants.domain')</th>
                        <th>@lang('saas::app.super-admin.tenants.price')</th>
                        <th>@lang('saas::app.super-admin.tenants.quantity')</th>
                        <th>@lang('saas::app.common.status')</th>
                        <th>@lang('saas::app.super-admin.tenants.category')</th>
                        <th>@lang('saas::app.super-admin.tenants.type')</th>
                        <th class="end">@lang('saas::app.super-admin.tenants.product-id')</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($products as $product)
                        @php
                            $image = $product->base_image
                                ? \Illuminate\Support\Facades\Storage::url($product->base_image)
                                : null;

                            $hostname = $tenantDirectory[$product->tenant_id] ?? null;
                        @endphp

                        <tr>
                            <td data-label="@lang('saas::app.super-admin.tenants.product')">
                                <div class="cell-media">
                                    <span class="cell-thumb">
                                        @if ($image)
                                            <img src="{{ $image }}" alt="{{ $product->name }}">
                                        @else
                                            @include('saas::layouts.partials.icon', ['name' => 'image'])
                                        @endif
                                    </span>

                                    <span>
                                        <span class="cell-title">{{ $product->name }}</span>

                                        <div class="cell-sub">{{ $product->sku }}</div>

                                        <div class="cell-sub">{{ $product->attribute_family_name }}</div>
                                    </span>
                                </div>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.domain')">
                                @if ($hostname)
                                    <a href="{{ route('saas.super-admin.tenants.show', $product->tenant_id) }}">{{ $hostname }}</a>
                                @elseif (is_null($product->tenant_id))
                                    <span class="badge neutral no-dot">@lang('saas::app.super-admin.audit.platform')</span>
                                @else
                                    {{ trans('saas::app.common.none') }}
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.price')" class="nowrap">
                                {{ number_format((float) ($product->special_price ?: $product->price), 2) }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.quantity')" class="nowrap">
                                {{ (int) $product->quantity }}
                            </td>

                            <td data-label="@lang('saas::app.common.status')">
                                @if ($product->status)
                                    <span class="badge active">@lang('saas::app.super-admin.tenants.enabled')</span>
                                @else
                                    <span class="badge neutral">@lang('saas::app.super-admin.tenants.disabled')</span>
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.category')">
                                {{ $product->category_name ? \Illuminate\Support\Str::limit($product->category_name, 40) : trans('saas::app.common.none') }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.type')">
                                <span class="badge info no-dot">{{ $product->type }}</span>
                            </td>

                            <td data-label="@lang('saas::app.super-admin.tenants.product-id')" class="end nowrap">
                                #{{ $product->product_id }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="dg-empty">
                                    @include('saas::layouts.partials.icon', ['name' => 'products'])

                                    <p>@lang('saas::app.super-admin.tenants.no-products')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="dg-footer">
                <span>
                    @lang('saas::app.super-admin.datagrid.page-of', [
                        'page' => $products->currentPage(),
                        'pages' => $products->lastPage(),
                    ])
                </span>

                <div class="pagination">{{ $products->links('saas::layouts.partials.pagination') }}</div>
            </div>
        @endif
    </div>
@endsection
