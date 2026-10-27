@extends('saas::marketing.layouts.app')

@section('content')
    <section class="page-hero">
        <div class="shell">
            <span class="eyebrow">@lang('saas::marketing.contact.eyebrow')</span>

            <h1>@lang('saas::marketing.contact.title')</h1>

            <p>@lang('saas::marketing.contact.description')</p>
        </div>
    </section>

    <section class="section">
        <div class="shell">
            <div class="form-grid">
                <div class="card">
                    <form method="POST" action="{{ route('saas.marketing.contact.store', [], false) }}" novalidate>
                        @csrf

                        <div class="honeypot" aria-hidden="true">
                            <label for="website">@lang('saas::marketing.contact.form.website')</label>
                            <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid grid-2" style="gap: 0 16px;">
                            <div class="field">
                                <label for="name">@lang('saas::marketing.contact.form.name')</label>

                                <input id="name" type="text" name="name" value="{{ old('name') }}" maxlength="120" required autocomplete="name">

                                @error('name')
                                    <p class="error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="email">@lang('saas::marketing.contact.form.email')</label>

                                <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="191" required autocomplete="email" dir="ltr">

                                @error('email')
                                    <p class="error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="phone">@lang('saas::marketing.contact.form.phone')</label>

                                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="40" autocomplete="tel" dir="ltr">

                                @error('phone')
                                    <p class="error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="company">@lang('saas::marketing.contact.form.company')</label>

                                <input id="company" type="text" name="company" value="{{ old('company') }}" maxlength="120" autocomplete="organization">

                                @error('company')
                                    <p class="error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label for="inquiry_type">@lang('saas::marketing.contact.form.inquiry_type')</label>

                            <select id="inquiry_type" name="inquiry_type" required>
                                <option value="">@lang('saas::marketing.contact.form.choose')</option>

                                @foreach ($inquiryTypes as $type)
                                    <option value="{{ $type }}" @selected(old('inquiry_type') === $type)>
                                        @lang('saas::marketing.contact.inquiry_types.'.$type)
                                    </option>
                                @endforeach
                            </select>

                            @error('inquiry_type')
                                <p class="error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="subject">@lang('saas::marketing.contact.form.subject')</label>

                            <input id="subject" type="text" name="subject" value="{{ old('subject') }}" maxlength="160" required>

                            @error('subject')
                                <p class="error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="message">@lang('saas::marketing.contact.form.message')</label>

                            <textarea id="message" name="message" maxlength="5000" required>{{ old('message') }}</textarea>

                            @error('message')
                                <p class="error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="check-line" for="privacy">
                                <input id="privacy" type="checkbox" name="privacy" value="1" required @checked(old('privacy'))>

                                <span>
                                    @lang('saas::marketing.contact.form.privacy_prefix')
                                    <a href="{{ route('saas.marketing.privacy', [], false) }}" target="_blank">{{ trans('saas::marketing.legal.privacy.title') }}</a>
                                    @lang('saas::marketing.contact.form.and')
                                    <a href="{{ route('saas.marketing.terms', [], false) }}" target="_blank">{{ trans('saas::marketing.legal.terms.title') }}</a>
                                </span>
                            </label>

                            @error('privacy')
                                <p class="error">{{ $message }}</p>
                            @enderror
                        </div>

                        <p class="muted small mb-2">@lang('saas::marketing.contact.form.legal_links')</p>

                        <x-marketing::button type="submit">
                            @lang('saas::marketing.contact.form.submit')
                            <x-marketing::icon name="arrow-right" size="16" />
                        </x-marketing::button>
                    </form>
                </div>

                <aside class="side-panel">
                    <h3>@lang('saas::marketing.contact.side.title')</h3>

                    <p class="mt-1">@lang('saas::marketing.contact.side.description')</p>

                    <ul>
                        @if ($site['contact_email'])
                            <li>
                                <span class="muted small">@lang('saas::marketing.contact.side.email')</span>
                                <div><a href="mailto:{{ $site['contact_email'] }}">{{ $site['contact_email'] }}</a></div>
                            </li>
                        @endif

                        @if ($site['contact_phone'])
                            <li>
                                <span class="muted small">@lang('saas::marketing.contact.side.phone')</span>
                                <div><a href="tel:{{ preg_replace('/[^0-9+]/', '', $site['contact_phone']) }}" dir="ltr">{{ $site['contact_phone'] }}</a></div>
                            </li>
                        @endif

                        <li>
                            <span class="muted small">@lang('saas::marketing.contact.side.response')</span>
                        </li>
                    </ul>

                    <h3 class="mt-3">@lang('saas::marketing.contact.side.links_title')</h3>

                    <ul>
                        <li><a href="{{ route('saas.marketing.pricing', [], false) }}">{{ trans('saas::marketing.nav.pricing') }}</a></li>
                        <li><a href="{{ route('saas.marketing.resources', [], false) }}#faq">{{ trans('saas::marketing.footer.faq') }}</a></li>
                        <li><a href="{{ route('saas.marketing.merchant-login', [], false) }}">{{ trans('saas::marketing.nav.merchant_login') }}</a></li>
                    </ul>
                </aside>
            </div>
        </div>
    </section>
@endsection
