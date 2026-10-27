@extends('saas::layouts.public')

@section('title', trans('saas::app.super-admin.login.title'))

@section('content')
    <div class="card" style="margin-top: 32px;">
        <div class="card-body">
            <h1 style="font-size: 20px; font-weight: 700; letter-spacing: -.01em;">@lang('saas::app.super-admin.login.title')</h1>

            <p class="muted" style="margin: 6px 0 18px;">@lang('saas::app.super-admin.login.subtitle')</p>

            <form method="POST" action="{{ route('saas.super-admin.login.store') }}">
                @csrf

                <div class="field">
                    <label for="email">@lang('saas::app.common.email')</label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="field">
                    <label for="password">@lang('saas::app.common.password')</label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <div class="field">
                    <label class="check">
                        <input name="remember" type="checkbox" value="1">

                        <span>@lang('saas::app.super-admin.login.remember')</span>
                    </label>
                </div>

                <button type="submit" class="btn" style="width: 100%; justify-content: center;">
                    @lang('saas::app.super-admin.login.submit')
                </button>
            </form>
        </div>
    </div>
@endsection
