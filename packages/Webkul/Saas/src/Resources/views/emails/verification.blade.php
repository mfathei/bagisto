@extends('saas::emails.layout')

@section('title', trans('saas::app.emails.verification.subject', ['platform' => config('saas.platform_name')]))

@section('content')
    <p style="margin: 0 0 12px;">@lang('saas::app.emails.verification.greeting', ['name' => $tenant->name])</p>

    <p style="margin: 0 0 12px;">@lang('saas::app.emails.verification.intro')</p>

    <p style="margin: 0 0 20px;">
        <a
            href="{{ $verificationUrl }}"
            style="display: inline-block; padding: 10px 18px; background: #2563eb; color: #ffffff; border-radius: 8px; font-weight: 600; text-decoration: none;"
        >@lang('saas::app.emails.verification.action')</a>
    </p>

    <p style="margin: 0 0 12px; font-size: 13px; color: #6b7280;">
        @lang('saas::app.emails.verification.link-help')
    </p>

    <p style="margin: 0; font-size: 13px; word-break: break-all; color: #2563eb;" dir="ltr">
        {{ $verificationUrl }}
    </p>

    <p style="margin: 20px 0 0; font-size: 13px; color: #6b7280;">
        @lang('saas::app.emails.verification.ignore')
    </p>
@endsection
