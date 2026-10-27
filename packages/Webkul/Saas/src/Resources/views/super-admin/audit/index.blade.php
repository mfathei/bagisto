@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.audit.title'))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.super-admin.audit.title')</h1>

            <p>@lang('saas::app.super-admin.audit.subtitle')</p>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('saas.super-admin.audit.index') }}" class="grid cols-4">
            <div class="field" style="margin: 0;">
                <label for="action">@lang('saas::app.super-admin.audit.action')</label>

                <select id="action" name="action">
                    <option value="">@lang('saas::app.super-admin.audit.all-actions')</option>

                    @foreach (\Webkul\Saas\Enums\AuditAction::cases() as $action)
                        <option
                            value="{{ $action->value }}"
                            @selected(($filters['action'] ?? '') === $action->value)
                        >{{ $action->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="margin: 0;">
                <label for="tenant_id">@lang('saas::app.super-admin.billing.tenant-id')</label>

                <input id="tenant_id" type="number" name="tenant_id" min="1" value="{{ $filters['tenant_id'] ?? '' }}">
            </div>

            <div class="field" style="margin: 0;">
                <label for="search">@lang('saas::app.super-admin.audit.search')</label>

                <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}">
            </div>

            <div class="actions" style="align-items: end;">
                <button type="submit" class="btn">@lang('saas::app.common.filter')</button>

                <a class="btn secondary" href="{{ route('saas.super-admin.audit.index') }}">
                    @lang('saas::app.common.reset')
                </a>
            </div>
        </form>
    </div>

    <div class="table-wrap" style="margin-top: 16px;">
        <table>
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
                        <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                        <td><code>{{ $log->action }}</code></td>
                        <td>
                            {{ $log->actor_name ?: $log->actor_type }}

                            @if ($log->actor_id)
                                <span class="muted">#{{ $log->actor_id }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($log->tenant_id)
                                <a href="{{ route('saas.super-admin.tenants.show', $log->tenant_id) }}">
                                    {{ $log->tenant?->name ?? '#'.$log->tenant_id }}
                                </a>
                            @else
                                <span class="muted">@lang('saas::app.super-admin.audit.platform')</span>
                            @endif
                        </td>
                        <td>
                            {{ $log->description }}

                            @if (! empty($log->metadata))
                                <details style="margin-top: 4px;">
                                    <summary class="muted">@lang('saas::app.super-admin.audit.metadata')</summary>

                                    <pre dir="ltr" style="white-space: pre-wrap; font-size: 12px;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @endif
                        </td>
                        <td dir="ltr">{{ $log->ip_address ?: trans('saas::app.common.none') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">@lang('saas::app.super-admin.audit.empty')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $logs->links() }}</div>
@endsection
