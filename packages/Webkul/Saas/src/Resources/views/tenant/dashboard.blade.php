<x-admin::layouts>
    <x-slot:title>
        @lang('saas::app.tenant.dashboard.title')
    </x-slot>

    @php
        $manualSteps = ['profile_completed', 'branding_completed', 'payment_configured', 'shipping_configured', 'published'];
        $completed = collect($onboarding)->where('completed', true)->count();
        $total = count($onboarding);
        $subscription = $tenant->currentSubscription;
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                {{ $tenant->name }}
            </p>

            <p class="text-gray-600 dark:text-gray-300">
                @lang('saas::app.tenant.dashboard.welcome', ['plan' => $tenant->plan?->name ?? trans('saas::app.common.none')])
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('saas.tenant.settings.index') }}" class="secondary-button">
                @lang('saas::app.tenant.nav.settings')
            </a>

            <a href="{{ route('saas.tenant.team.index') }}" class="secondary-button">
                @lang('saas::app.tenant.nav.team')
            </a>
        </div>
    </div>

    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.common.plan')</p>

            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">
                {{ $tenant->plan?->name ?? trans('saas::app.common.none') }}
            </p>
        </div>

        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.tenant.dashboard.subscription')</p>

            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">
                {{ $subscription?->status->value ?? trans('saas::app.common.none') }}
            </p>

            @if ($subscription?->trial_ends_at && $subscription->status === \Webkul\Saas\Enums\SubscriptionStatus::Trialing)
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                    @lang('saas::app.tenant.dashboard.trial-ends', ['date' => $subscription->trial_ends_at->format('Y-m-d')])
                </p>
            @elseif ($subscription?->current_period_end)
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                    @lang('saas::app.tenant.dashboard.renews', ['date' => $subscription->current_period_end->format('Y-m-d')])
                </p>
            @endif
        </div>

        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.tenant.dashboard.storefront')</p>

            @if ($url = $tenant->storefrontUrl())
                <a
                    href="{{ $url }}"
                    target="_blank"
                    rel="noopener"
                    class="mt-1 block break-all text-sm font-semibold text-blue-600 hover:underline dark:text-blue-400"
                    dir="ltr"
                >{{ $url }}</a>
            @else
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @lang('saas::app.tenant.dashboard.no-domain')
                </p>
            @endif
        </div>

        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.tenant.dashboard.status')</p>

            <p class="mt-1 text-lg font-semibold capitalize text-gray-800 dark:text-white">
                {{ $tenant->status->value }}
            </p>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-4 max-xl:flex-col">
        <div class="flex flex-1 flex-col gap-4">
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('saas::app.tenant.dashboard.onboarding')
                    </p>

                    <span class="text-sm text-gray-600 dark:text-gray-300">
                        {{ $completed }}/{{ $total }}
                    </span>
                </div>

                <form method="POST" action="{{ route('saas.tenant.onboarding.update') }}" class="mt-3">
                    @csrf

                    <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($onboarding as $step)
                            <li class="flex items-center justify-between gap-3 py-2.5">
                                <span class="{{ $step['completed'] ? 'text-gray-500 line-through dark:text-gray-400' : 'text-gray-800 dark:text-white' }}">
                                    {{ $step['label'] }}
                                </span>

                                @if (in_array($step['key'], $manualSteps, true))
                                    <input type="hidden" name="{{ $step['key'] }}" value="0">

                                    <input
                                        type="checkbox"
                                        name="{{ $step['key'] }}"
                                        value="1"
                                        @checked($step['completed'])
                                    >
                                @else
                                    <span class="text-xs font-semibold {{ $step['completed'] ? 'text-green-600' : 'text-gray-400' }}">
                                        {{ $step['completed'] ? trans('saas::app.common.yes') : trans('saas::app.common.no') }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <button type="submit" class="primary-button mt-4">
                        @lang('saas::app.tenant.onboarding.save')
                    </button>
                </form>
            </div>

            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('saas::app.tenant.dashboard.recent-activity')
                    </p>

                    <a href="{{ route('saas.tenant.usage.index') }}" class="text-sm text-blue-600 dark:text-blue-400">
                        @lang('saas::app.tenant.nav.usage')
                    </a>
                </div>

                <ul class="mt-3 divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($recentAudit as $log)
                        <li class="flex items-start justify-between gap-3 py-2.5">
                            <div>
                                <p class="text-sm text-gray-800 dark:text-white">{{ $log->description }}</p>

                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $log->action }}</p>
                            </div>

                            <span class="whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                {{ $log->created_at?->format('Y-m-d H:i') }}
                            </span>
                        </li>
                    @empty
                        <li class="py-2.5 text-sm text-gray-500 dark:text-gray-400">
                            @lang('saas::app.tenant.dashboard.no-activity')
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="flex w-full flex-col gap-4 xl:w-96">
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('saas::app.tenant.dashboard.usage')
                </p>

                <div class="mt-3 grid gap-3">
                    @foreach (['products' => 'products', 'orders' => 'orders', 'customers' => 'customers', 'staff' => 'staff'] as $key => $label)
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-300">
                                    @lang('saas::app.tenant.usage.metrics.'.$label)
                                </span>

                                <span class="font-semibold text-gray-800 dark:text-white">
                                    {{ number_format((float) ($usage[$key] ?? 0), 0) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('saas.tenant.usage.index') }}" class="mt-4 inline-block text-sm text-blue-600 dark:text-blue-400">
                    @lang('saas::app.tenant.dashboard.view-usage')
                </a>
            </div>

            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('saas::app.tenant.nav.shortcuts')
                </p>

                <div class="mt-3 grid gap-2">
                    <a href="{{ route('saas.tenant.domains.index') }}" class="secondary-button justify-center">
                        @lang('saas::app.tenant.nav.domains')
                    </a>

                    <a href="{{ route('saas.tenant.billing.index') }}" class="secondary-button justify-center">
                        @lang('saas::app.tenant.nav.billing')
                    </a>

                    @if ($url = $tenant->storefrontUrl())
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="secondary-button justify-center">
                            @lang('saas::app.tenant.dashboard.open-storefront')
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin::layouts>
