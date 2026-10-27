@extends('saas::marketing.layouts.app')

@section('content')
    <section class="page-hero">
        <div class="shell">
            <span class="eyebrow">@lang('saas::marketing.legal.eyebrow')</span>

            <h1>@lang('saas::marketing.legal.'.$page.'.title')</h1>
        </div>
    </section>

    <section class="section">
        <div class="shell">
            <div class="prose">
                <div class="notice" role="note">
                    @lang('saas::marketing.legal.notice')
                </div>

                @foreach ($sections as $section)
                    <section>
                        <h2>{{ $section['heading'] }}</h2>

                        @foreach ((array) ($section['body'] ?? []) as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </section>
                @endforeach

                <p class="mt-3">
                    <a href="{{ route('saas.marketing.contact', [], false) }}">
                        @lang('saas::marketing.legal.questions')
                    </a>
                </p>
            </div>
        </div>
    </section>
@endsection
