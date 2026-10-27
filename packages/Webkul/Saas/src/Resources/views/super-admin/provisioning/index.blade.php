@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.provisioning.title'))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.super-admin.provisioning.title')</h1>

            <p>@lang('saas::app.super-admin.provisioning.subtitle')</p>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('saas.super-admin.provisioning.index') }}" class="grid cols-3">
            <div class="field" style="margin: 0;">
                <label for="status">@lang('saas::app.common.status')</label>

                <select id="status" name="status">
                    <option value="">@lang('saas::app.super-admin.provisioning.all-statuses')</option>

                    @foreach (\Webkul\Saas\Enums\ProvisioningStatus::cases() as $case)
                        <option
                            value="{{ $case->value }}"
                            @selected(($status ?? '') === $case->value)
                        >{{ $case->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="actions" style="align-items: end;">
                <button type="submit" class="btn">@lang('saas::app.common.filter')</button>

                <a class="btn secondary" href="{{ route('saas.super-admin.provisioning.index') }}">
                    @lang('saas::app.common.reset')
                </a>
            </div>
        </form>
    </div>

    <div class="table-wrap" style="margin-top: 16px;">
        <table>
            <thead>
                <tr>
                    <th>@lang('saas::app.common.tenant')</th>
                    <th>@lang('saas::app.super-admin.provisioning.type')</th>
                    <th>@lang('saas::app.common.status')</th>
                    <th>@lang('saas::app.super-admin.provisioning.step')</th>
                    <th>@lang('saas::app.super-admin.provisioning.attempts')</th>
                    <th>@lang('saas::app.super-admin.provisioning.started')</th>
                    <th>@lang('saas::app.common.actions')</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($operations as $operation)
                    <tr>
                        <td>
                            <a href="{{ route('saas.super-admin.tenants.show', $operation->tenant_id) }}">
                                {{ $operation->tenant?->name ?? '#'.$operation->tenant_id }}
                            </a>
                        </td>
                        <td>{{ $operation->type }}</td>
                        <td><span class="badge {{ $operation->status->value }}">{{ $operation->status->value }}</span></td>
                        <td>
                            {{ $operation->step ?: trans('saas::app.common.none') }}

                            @if ($operation->error)
                                <p class="muted" style="margin: 4px 0 0;">{{ \Illuminate\Support\Str::limit($operation->error, 180) }}</p>
                            @endif
                        </td>
                        <td>{{ $operation->attempts }}</td>
                        <td>{{ $operation->started_at?->format('Y-m-d H:i') ?? trans('saas::app.common.none') }}</td>
                        <td>
                            @if ($operation->status === \Webkul\Saas\Enums\ProvisioningStatus::Failed)
                                <form method="POST" action="{{ route('saas.super-admin.provisioning.retry', $operation->getKey()) }}" class="inline-form">
                                    @csrf

                                    <button type="submit" class="btn small">@lang('saas::app.super-admin.provisioning.retry')</button>
                                </form>
                            @else
                                <span class="muted">@lang('saas::app.common.none')</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">@lang('saas::app.super-admin.provisioning.empty')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $operations->links() }}</div>
@endsection
