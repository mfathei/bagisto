@extends('saas::layouts.public')

@section('title', trans('saas::app.invitation.title', ['store' => $tenant->name]))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.invitation.title', ['store' => $tenant->name])</h1>

            <p>@lang('saas::app.invitation.subtitle', ['email' => $invitation->email])</p>
        </div>
    </div>

    <div class="card">
        <dl class="kv">
            <dt>@lang('saas::app.invitation.store')</dt>
            <dd>{{ $tenant->name }}</dd>

            <dt>@lang('saas::app.invitation.email')</dt>
            <dd dir="ltr">{{ $invitation->email }}</dd>

            <dt>@lang('saas::app.invitation.role')</dt>
            <dd>{{ $invitation->role->value }}</dd>

            <dt>@lang('saas::app.invitation.expires')</dt>
            <dd>{{ $invitation->expires_at?->format('Y-m-d H:i') }}</dd>
        </dl>

        <form method="POST" action="{{ route('saas.invitation.accept', $token) }}" style="margin-top: 16px;">
            @csrf

            <div class="field">
                <label for="name">@lang('saas::app.invitation.your-name')</label>

                <input id="name" type="text" name="name" maxlength="150" value="{{ old('name') }}" required autofocus>

                @error('name')
                    <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="password">@lang('saas::app.registration.password')</label>

                <input id="password" type="password" name="password" required autocomplete="new-password">

                <p class="muted" style="margin: 6px 0 0;">@lang('saas::app.registration.password-help', ['min' => config('saas.registration.password_min_length', 12)])</p>

                @error('password')
                    <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">@lang('saas::app.registration.password-confirmation')</label>

                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>

            @error('email')
                <p class="alert error">{{ $message }}</p>
            @enderror

            @error('invitation')
                <p class="alert error">{{ $message }}</p>
            @enderror

            <button type="submit" class="btn">@lang('saas::app.invitation.accept')</button>
        </form>
    </div>
@endsection
