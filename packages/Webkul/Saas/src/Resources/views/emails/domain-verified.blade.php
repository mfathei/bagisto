@extends('saas::emails.layout')

@section('title', trans('saas::app.emails.domain-verified.subject'))

@section('content')
    <p style="margin: 0 0 12px;">@lang('saas::app.emails.domain-verified.greeting', ['name' => $tenant->name])</p>

    <p style="margin: 0 0 12px;">
        @lang('saas::app.emails.domain-verified.intro', ['hostname' => $hostname])
    </p>

    <p style="margin: 0 0 12px; font-weight: 600;" dir="ltr">{{ $hostname }}</p>

    <p style="margin: 0; font-size: 13px; color: #6b7280;">
        @lang('saas::app.emails.domain-verified.ssl')
    </p>
@endsection
