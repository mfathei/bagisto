<x-admin::layouts>
    <x-slot:title>
        @lang('saas::app.tenant.domains.title')
    </x-slot>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('saas::app.tenant.domains.title')
            </p>

            <p class="text-gray-600 dark:text-gray-300">
                @lang('saas::app.tenant.domains.subtitle')
            </p>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-4 max-xl:flex-col">
        <div class="flex flex-1 flex-col gap-4">
            @forelse ($domains as $domain)
                <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-base font-semibold text-gray-800 dark:text-white" dir="ltr">
                                {{ $domain->hostname }}

                                @if ($domain->is_primary)
                                    <span class="ms-2 rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                                        @lang('saas::app.tenant.domains.primary')
                                    </span>
                                @endif
                            </p>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                {{ $domain->type->label() }}
                                &middot;
                                <span class="font-semibold">{{ $domain->status->value }}</span>
                                &middot;
                                @lang('saas::app.tenant.domains.ssl') {{ $domain->ssl_status->value }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($domain->status !== \Webkul\Saas\Enums\DomainStatus::Verified)
                                <form method="POST" action="{{ route('saas.tenant.domains.verify', $domain->getKey()) }}">
                                    @csrf

                                    <button type="submit" class="primary-button text-xs">
                                        @lang('saas::app.tenant.domains.verify')
                                    </button>
                                </form>
                            @elseif (! $domain->is_primary)
                                <form method="POST" action="{{ route('saas.tenant.domains.primary', $domain->getKey()) }}">
                                    @csrf

                                    <button type="submit" class="secondary-button text-xs">
                                        @lang('saas::app.tenant.domains.make-primary')
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('saas.tenant.domains.destroy', $domain->getKey()) }}" onsubmit="return confirm('@lang('saas::app.tenant.domains.confirm-remove')');">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="text-xs font-semibold text-red-600">
                                    @lang('saas::app.tenant.domains.remove')
                                </button>
                            </form>
                        </div>
                    </div>

                    @if ($domain->failure_reason)
                        <p class="mt-3 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
                            {{ $domain->failure_reason }}
                        </p>
                    @endif

                    @if ($domain->status !== \Webkul\Saas\Enums\DomainStatus::Verified)
                        @php
                            $challenge = $domain->challenges->first();
                        @endphp

                        @if ($challenge)
                            <div class="mt-3 rounded-md bg-gray-50 p-3 text-sm dark:bg-gray-800">
                                <p class="font-semibold text-gray-800 dark:text-white">
                                    @lang('saas::app.tenant.domains.challenge')
                                </p>

                                <dl class="mt-2 grid gap-1 text-gray-600 dark:text-gray-300">
                                    <div class="flex flex-wrap gap-2">
                                        <dt class="font-semibold">@lang('saas::app.tenant.domains.record-type')</dt>
                                        <dd dir="ltr">{{ $challenge->type->value }}</dd>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <dt class="font-semibold">@lang('saas::app.tenant.domains.record-name')</dt>
                                        <dd dir="ltr"><code>{{ $domain->hostname }}</code></dd>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <dt class="font-semibold">@lang('saas::app.tenant.domains.record-value')</dt>
                                        <dd class="break-all" dir="ltr"><code>{{ $challenge->expected_value ?: $challenge->token }}</code></dd>
                                    </div>
                                </dl>

                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    @lang('saas::app.tenant.domains.challenge-help')
                                </p>
                            </div>
                        @endif
                    @endif

                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        @lang('saas::app.tenant.domains.last-checked'):
                        {{ $domain->last_checked_at?->format('Y-m-d H:i') ?? trans('saas::app.common.none') }}
                    </p>
                </div>
            @empty
                <div class="box-shadow rounded-lg border border-gray-200 bg-white p-6 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                    @lang('saas::app.tenant.domains.empty')
                </div>
            @endforelse
        </div>

        <div class="w-full xl:w-96">
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('saas::app.tenant.domains.add')
                </p>

                <form method="POST" action="{{ route('saas.tenant.domains.store') }}" class="mt-3 grid gap-3">
                    @csrf

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="hostname">
                            @lang('saas::app.tenant.domains.hostname')
                        </label>

                        <input id="hostname" type="text" name="hostname" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" maxlength="191" value="{{ old('hostname') }}" placeholder="shop.example.com" dir="ltr" required>

                        @error('hostname')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="primary-button">
                        @lang('saas::app.tenant.domains.add-domain')
                    </button>
                </form>

                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    @lang('saas::app.tenant.domains.add-help', ['suffix' => $suffix])
                </p>
            </div>
        </div>
    </div>
</x-admin::layouts>
