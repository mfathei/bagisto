@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.tenants.title'))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.super-admin.tenants.title')</h1>

            <p>@lang('saas::app.super-admin.tenants.subtitle')</p>
        </div>

        <a class="btn" href="{{ route('saas.super-admin.tenants.create') }}">
            @lang('saas::app.super-admin.tenants.new')
        </a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('saas.super-admin.tenants.index') }}" class="grid cols-4">
            <div class="field" style="margin: 0;">
                <label for="search">@lang('saas::app.common.search')</label>

                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="@lang('saas::app.super-admin.tenants.search-placeholder')"
                >
            </div>

            <div class="field" style="margin: 0;">
                <label for="status">@lang('saas::app.common.status')</label>

                <select id="status" name="status">
                    <option value="">@lang('saas::app.super-admin.tenants.all-statuses')</option>

                    @foreach (\Webkul\Saas\Enums\TenantStatus::cases() as $status)
                        <option
                            value="{{ $status->value }}"
                            @selected(($filters['status'] ?? '') === $status->value)
                        >{{ $status->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="margin: 0;">
                <label for="plan_id">@lang('saas::app.common.plan')</label>

                <select id="plan_id" name="plan_id">
                    <option value="">@lang('saas::app.super-admin.tenants.all-plans')</option>

                    @foreach ($plans as $plan)
                        <option
                            value="{{ $plan->getKey() }}"
                            @selected((string) ($filters['plan_id'] ?? '') === (string) $plan->getKey())
                        >{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="actions" style="align-items: end;">
                <button type="submit" class="btn">@lang('saas::app.common.filter')</button>

                <a class="btn secondary" href="{{ route('saas.super-admin.tenants.index') }}">
                    @lang('saas::app.common.reset')
                </a>
            </div>
        </form>
    </div>

    <div class="table-wrap" style="margin-top: 16px;">
        <table>
            <thead>
                <tr>
                    <th>@lang('saas::app.common.name')</th>
                    <th>@lang('saas::app.super-admin.tenants.slug')</th>
                    <th>@lang('saas::app.common.status')</th>
                    <th>@lang('saas::app.common.plan')</th>
                    <th>@lang('saas::app.common.created')</th>
                    <th>@lang('saas::app.common.actions')</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($tenants as $tenant)
                    <tr>
                        <td>
                            <a href="{{ route('saas.super-admin.tenants.show', $tenant->getKey()) }}">
                                {{ $tenant->name }}
                            </a>

                            <div class="muted">{{ $tenant->email }}</div>
                        </td>

                        <td>{{ $tenant->slug }}</td>

                        <td>
                            <span class="badge {{ $tenant->status->value }}">{{ $tenant->status->value }}</span>
                        </td>

                        <td>{{ $tenant->plan?->name ?? trans('saas::app.common.none') }}</td>

                        <td>{{ $tenant->created_at?->format('Y-m-d') }}</td>

                        <td>
                            <a class="btn small secondary" href="{{ route('saas.super-admin.tenants.show', $tenant->getKey()) }}">
                                @lang('saas::app.common.view')
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">@lang('saas::app.super-admin.tenants.empty')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $tenants->links() }}</div>
@endsection
