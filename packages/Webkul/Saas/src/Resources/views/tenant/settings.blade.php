<x-admin::layouts>
    <x-slot:title>
        @lang('saas::app.tenant.settings.title')
    </x-slot>

    @php
        $settings = (array) $tenant->settings;
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('saas::app.tenant.settings.title')
            </p>

            <p class="text-gray-600 dark:text-gray-300">
                @lang('saas::app.tenant.settings.subtitle')
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('saas.tenant.settings.update') }}" class="mt-4">
        @csrf
        @method('PUT')

        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-base font-semibold text-gray-800 dark:text-white">
                @lang('saas::app.tenant.settings.profile')
            </p>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="name">
                        @lang('saas::app.tenant.settings.name')
                    </label>

                    <input id="name" type="text" name="name" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" maxlength="150" value="{{ old('name', $tenant->name) }}" required>

                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="legal_name">
                        @lang('saas::app.tenant.settings.legal-name')
                    </label>

                    <input id="legal_name" type="text" name="legal_name" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" maxlength="150" value="{{ old('legal_name', $tenant->legal_name) }}">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="email">
                        @lang('saas::app.tenant.settings.email')
                    </label>

                    <input id="email" type="email" name="email" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" maxlength="191" value="{{ old('email', $tenant->email) }}">

                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="phone">
                        @lang('saas::app.tenant.settings.phone')
                    </label>

                    <input id="phone" type="text" name="phone" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" maxlength="40" value="{{ old('phone', $tenant->phone) }}">
                </div>
            </div>
        </div>

        <div class="box-shadow mt-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-base font-semibold text-gray-800 dark:text-white">
                @lang('saas::app.tenant.settings.localization')
            </p>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="timezone">
                        @lang('saas::app.tenant.settings.timezone')
                    </label>

                    <select id="timezone" name="timezone" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" required>
                        @foreach ($timezones as $timezone)
                            <option value="{{ $timezone }}" @selected(old('timezone', $tenant->timezone) === $timezone)>
                                {{ $timezone }}
                            </option>
                        @endforeach
                    </select>

                    @error('timezone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="default_locale">
                        @lang('saas::app.tenant.settings.locale')
                    </label>

                    <select id="default_locale" name="default_locale" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" required>
                        @foreach ($locales as $locale)
                            <option value="{{ $locale->code }}" @selected(old('default_locale', $tenant->default_locale) === $locale->code)>
                                {{ $locale->name }} ({{ $locale->code }})
                            </option>
                        @endforeach
                    </select>

                    @error('default_locale')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="default_currency">
                        @lang('saas::app.tenant.settings.currency')
                    </label>

                    <select id="default_currency" name="default_currency" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" required>
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency->code }}" @selected(old('default_currency', $tenant->default_currency) === $currency->code)>
                                {{ $currency->name }} ({{ $currency->code }})
                            </option>
                        @endforeach
                    </select>

                    @error('default_currency')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="box-shadow mt-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-base font-semibold text-gray-800 dark:text-white">
                @lang('saas::app.tenant.settings.contact')
            </p>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="md:col-span-3">
                    <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="tagline">
                        @lang('saas::app.tenant.settings.tagline')
                    </label>

                    <input id="tagline" type="text" name="tagline" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" maxlength="255" value="{{ old('tagline', $settings['tagline'] ?? '') }}">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="contact_email">
                        @lang('saas::app.tenant.settings.contact-email')
                    </label>

                    <input id="contact_email" type="email" name="contact_email" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" maxlength="191" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}">

                    @error('contact_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="contact_phone">
                        @lang('saas::app.tenant.settings.contact-phone')
                    </label>

                    <input id="contact_phone" type="text" name="contact_phone" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" maxlength="40" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}">
                </div>
            </div>
        </div>

        <button type="submit" class="primary-button mt-4">
            @lang('saas::app.common.save')
        </button>
    </form>
</x-admin::layouts>
