@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.plans.title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.nav.plans-list')</span>
            </div>

            <h1>@lang('saas::app.super-admin.plans.title')</h1>

            <p>@lang('saas::app.super-admin.plans.subtitle')</p>
        </div>

        <div class="page-actions">
            <a class="btn" href="{{ route('saas.super-admin.plans.create') }}">
                @include('saas::layouts.partials.icon', ['name' => 'plus'])

                @lang('saas::app.super-admin.plans.new')
            </a>
        </div>
    </div>

    <div class="datagrid">
        <div class="dg-toolbar">
            <div class="dg-toolbar-left">
                <span class="dg-count">
                    @if ($plans->isNotEmpty())
                        {{ trans_choice('saas::app.super-admin.datagrid.records', $plans->count(), ['count' => $plans->count()]) }}
                    @else
                        @lang('saas::app.super-admin.datagrid.no-records')
                    @endif
                </span>
            </div>

        </div>

        <div style="overflow-x: auto;">
            <table class="dg-table">
                <thead>
                    <tr>
                        <th class="dg-check">
                            <input type="checkbox" class="dg-check-all" aria-label="@lang('saas::app.super-admin.datagrid.select-all')">
                        </th>
                        <th>@lang('saas::app.super-admin.plans.id')</th>
                        <th>@lang('saas::app.super-admin.plans.code')</th>
                        <th>@lang('saas::app.common.name')</th>
                        <th>@lang('saas::app.super-admin.plans.monthly-amount')</th>
                        <th>@lang('saas::app.super-admin.plans.yearly-amount')</th>
                        <th>@lang('saas::app.common.status')</th>
                        <th class="end">@lang('saas::app.common.actions')</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($plans as $plan)
                        <tr>
                            <td class="dg-check">
                                <input type="checkbox" class="dg-check-row" value="{{ $plan->getKey() }}" aria-label="{{ $plan->name }}">
                            </td>

                            <td data-label="@lang('saas::app.super-admin.plans.id')" class="nowrap">
                                #{{ $plan->getKey() }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.plans.code')">
                                <span class="badge neutral no-dot">{{ $plan->slug }}</span>
                            </td>

                            <td data-label="@lang('saas::app.common.name')">
                                <a class="cell-title" href="{{ route('saas.super-admin.plans.show', $plan->getKey()) }}">{{ $plan->name }}</a>

                                @if ($plan->description)
                                    <div class="cell-sub">{{ \Illuminate\Support\Str::limit($plan->description, 60) }}</div>
                                @endif
                            </td>

                            <td data-label="@lang('saas::app.super-admin.plans.monthly-amount')" class="nowrap">
                                {{ $plan->price }} {{ $plan->currency }}
                            </td>

                            <td data-label="@lang('saas::app.super-admin.plans.yearly-amount')" class="nowrap">
                                {{ number_format($plan->yearlyAmount(), 2) }} {{ $plan->currency }}
                            </td>

                            <td data-label="@lang('saas::app.common.status')">
                                @if ($plan->is_active)
                                    <span class="badge active">@lang('saas::app.super-admin.plans.active')</span>
                                @else
                                    <span class="badge neutral">@lang('saas::app.super-admin.plans.inactive')</span>
                                @endif

                                @if ($plan->hasOffer())
                                    <span class="badge info no-dot">{{ $plan->offer_title ?: trans('saas::app.super-admin.plans.offer') }}</span>
                                @endif
                            </td>

                            <td class="end" data-label="@lang('saas::app.common.actions')">
                                <div class="actions" style="justify-content: flex-end;">
                                    <a
                                        class="icon-btn bordered"
                                        href="{{ route('saas.super-admin.plans.show', $plan->getKey()) }}"
                                        title="@lang('saas::app.common.view')"
                                    >
                                        @include('saas::layouts.partials.icon', ['name' => 'view'])
                                    </a>

                                    <a
                                        class="icon-btn bordered"
                                        href="{{ route('saas.super-admin.plans.edit', $plan->getKey()) }}"
                                        title="@lang('saas::app.common.edit')"
                                    >
                                        @include('saas::layouts.partials.icon', ['name' => 'edit'])
                                    </a>

                                    <form
                                        class="inline-form"
                                        method="POST"
                                        action="{{ route('saas.super-admin.plans.destroy', $plan->getKey()) }}"
                                        data-confirm="@lang('saas::app.super-admin.plans.delete-confirm')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="icon-btn bordered" title="@lang('saas::app.common.delete')">
                                            @include('saas::layouts.partials.icon', ['name' => 'trash'])
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="dg-empty">
                                    @include('saas::layouts.partials.icon', ['name' => 'plans'])

                                    <p>@lang('saas::app.super-admin.plans.empty')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
