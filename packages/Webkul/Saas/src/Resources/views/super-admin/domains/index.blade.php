@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.domains.title'))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.super-admin.domains.title')</h1>

            <p>@lang('saas::app.super-admin.domains.subtitle')</p>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('saas.super-admin.domains.index') }}" class="grid cols-4">
            <div class="field" style="margin: 0;">
                <label for="status">@lang('saas::app.common.status')</label>

                <select id="status" name="status">
                    <option value="">@lang('saas::app.super-admin.domains.all-statuses')</option>

                    @foreach (\Webkul\Saas\Enums\DomainStatus::cases() as $status)
                        <option
                            value="{{ $status->value }}"
                            @selected(($filters['status'] ?? '') === $status->value)
                        >{{ $status->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="margin: 0;">
                <label for="type">@lang('saas::app.super-admin.domains.type')</label>

                <select id="type" name="type">
                    <option value="">@lang('saas::app.super-admin.domains.all-types')</option>

                    @foreach (\Webkul\Saas\Enums\DomainType::cases() as $type)
                        <option
                            value="{{ $type->value }}"
                            @selected(($filters['type'] ?? '') === $type->value)
                        >{{ $type->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="margin: 0;">
                <label for="search">@lang('saas::app.super-admin.domains.hostname')</label>

                <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}">
            </div>

            <div class="actions" style="align-items: end;">
                <button type="submit" class="btn">@lang('saas::app.common.filter')</button>

                <a class="btn secondary" href="{{ route('saas.super-admin.domains.index') }}">
                    @lang('saas::app.common.reset')
                </a>
            </div>
        </form>
    </div>

    <div class="table-wrap" style="margin-top: 16px;">
        <table>
            <thead>
                <tr>
                    <th>@lang('saas::app.super-admin.domains.hostname')</th>
                    <th>@lang('saas::app.common.tenant')</th>
                    <th>@lang('saas::app.super-admin.domains.type')</th>
                    <th>@lang('saas::app.common.status')</th>
                    <th>@lang('saas::app.super-admin.domains.ssl')</th>
                    <th>@lang('saas::app.super-admin.domains.last-checked')</th>
                    <th>@lang('saas::app.common.actions')</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($domains as $domain)
                    <tr>
                        <td>
                            <strong>{{ $domain->hostname }}</strong>

                            @if ($domain->is_primary)
                                <span class="badge active">@lang('saas::app.super-admin.domains.primary')</span>
                            @endif

                            @if ($domain->failure_reason)
                                <p class="muted" style="margin: 4px 0 0;">{{ $domain->failure_reason }}</p>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('saas.super-admin.tenants.show', $domain->tenant_id) }}">
                                {{ $domain->tenant?->name ?? '#'.$domain->tenant_id }}
                            </a>
                        </td>
                        <td>{{ $domain->type->value }}</td>
                        <td><span class="badge {{ $domain->status->value }}">{{ $domain->status->value }}</span></td>
                        <td><span class="badge">{{ $domain->ssl_status->value }}</span></td>
                        <td>{{ $domain->last_checked_at?->format('Y-m-d H:i') ?? trans('saas::app.common.none') }}</td>
                        <td>
                            <div class="actions">
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
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">@lang('saas::app.super-admin.domains.empty')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $domains->links() }}</div>
@endsection
