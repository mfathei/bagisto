@extends('saas::emails.layout')

@section('title', trans('saas::app.emails.approved.subject'))

@section('content')
    <p style="margin: 0 0 12px;">@lang('saas::app.emails.approved.greeting', ['name' => $tenant->name])</p>

    <p style="margin: 0 0 12px;">@lang('saas::app.emails.approved.intro', ['platform' => config('saas.platform_name')])</p>

    @if ($storefrontUrl)
        <p style="margin: 0 0 12px;">@lang('saas::app.emails.approved.storefront')</p>

        <p style="margin: 0 0 20px;">
            <a
                href="{{ $storefrontUrl }}"
                style="display: inline-block; padding: 10px 18px; background: #2563eb; color: #ffffff; border-radius: 8px; font-weight: 600; text-decoration: none;"
            >{{ $storefrontUrl }}</a>
        </p>
    @endif

    <p style="margin: 0 0 12px;">@lang('saas::app.emails.approved.admin')</p>

    <p style="margin: 0; font-size: 13px; color: #6b7280;">
        @lang('saas::app.emails.approved.support', ['email' => config('saas.settings.support_email')])
    </p>
@endsection
