@extends('saas::layouts.public')

@section('title', trans('saas::app.registration.pending-title', ['platform' => config('saas.platform_name')]))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.registration.pending-title', ['platform' => config('saas.platform_name')])</h1>

            <p>@lang('saas::app.registration.pending-subtitle', ['store' => $tenant->name])</p>
        </div>
    </div>

    <div class="card">
        <dl class="kv">
            <dt>@lang('saas::app.registration.store-name')</dt>
            <dd>{{ $tenant->name }}</dd>

            <dt>@lang('saas::app.registration.store-slug')</dt>
            <dd dir="ltr">{{ $tenant->slug }}</dd>

            <dt>@lang('saas::app.common.status')</dt>
            <dd><span class="badge {{ $tenant->status->value }}">{{ $tenant->status->value }}</span></dd>

            @if ($requiresVerification)
                <dt>@lang('saas::app.registration.email-verified')</dt>
                <dd>
                    @if ($tenant->email_verified_at)
                        <span class="badge verified">@lang('saas::app.common.yes')</span>
                    @else
                        <span class="badge pending">@lang('saas::app.common.no')</span>
                    @endif
                </dd>
            @endif

            @if ($requiresApproval)
                <dt>@lang('saas::app.registration.approval')</dt>
                <dd>@lang('saas::app.registration.approval-help')</dd>
            @endif
        </dl>

        @if ($requiresVerification && ! $tenant->email_verified_at)
            <div class="alert info" style="margin-top: 16px;">
                @lang('saas::app.registration.verification-sent', ['email' => $verificationEmail])
            </div>

            <form method="POST" action="{{ route('saas.registration.resend') }}">
                @csrf

                <input type="hidden" name="email" value="{{ $verificationEmail }}">

                <button type="submit" class="btn secondary">@lang('saas::app.registration.resend')</button>
            </form>
        @endif

        <p class="muted" style="margin-top: 16px;">
            @lang('saas::app.registration.pending-refresh')
        </p>
    </div>
@endsection
