@extends('saas::layouts.public')

@section('title', trans('saas::app.super-admin.login.title'))

@section('content')
    <div class="card">
        <h1 style="margin-top: 0;">@lang('saas::app.super-admin.login.title')</h1>

        <p class="muted">@lang('saas::app.super-admin.login.subtitle')</p>

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

                    @lang('saas::app.super-admin.login.remember')
                </label>
            </div>

            <button type="submit" class="btn">@lang('saas::app.super-admin.login.submit')</button>
        </form>
    </div>
@endsection
