<x-admin::layouts>
    <x-slot:title>
        @lang('saas::app.tenant.billing.title')
    </x-slot>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('saas::app.tenant.billing.title')
            </p>

            <p class="text-gray-600 dark:text-gray-300">
                @lang('saas::app.tenant.billing.subtitle')
            </p>
        </div>
    </div>

    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.common.plan')</p>

            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">
                {{ $subscription?->plan?->name ?? $tenant->plan?->name ?? trans('saas::app.common.none') }}
            </p>
        </div>

        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.tenant.billing.status')</p>

            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">
                {{ $subscription?->status->value ?? trans('saas::app.common.none') }}
            </p>
        </div>

        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.tenant.billing.period')</p>

            <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
                {{ $subscription?->current_period_start?->format('Y-m-d') ?? trans('saas::app.common.none') }}
                &ndash;
                {{ $subscription?->current_period_end?->format('Y-m-d') ?? trans('saas::app.common.none') }}
            </p>
        </div>

        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.tenant.billing.outstanding')</p>

            <p class="mt-1 text-lg font-semibold {{ $outstanding > 0 ? 'text-red-600' : 'text-gray-800 dark:text-white' }}">
                {{ number_format((float) $outstanding, 2) }} {{ config('saas.billing.currency') }}
            </p>
        </div>
    </div>

    @if ($subscription?->trial_ends_at && $subscription->status === \Webkul\Saas\Enums\SubscriptionStatus::Trialing)
        <div class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
            @lang('saas::app.tenant.billing.trial-ends', ['date' => $subscription->trial_ends_at->format('Y-m-d')])
        </div>
    @endif

    <div class="mt-4 box-shadow overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="w-full text-left">
            <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3">@lang('saas::app.tenant.billing.invoice')</th>
                    <th class="px-4 py-3">@lang('saas::app.common.status')</th>
                    <th class="px-4 py-3">@lang('saas::app.common.total')</th>
                    <th class="px-4 py-3">@lang('saas::app.common.balance')</th>
                    <th class="px-4 py-3">@lang('saas::app.super-admin.billing.due')</th>
                    <th class="px-4 py-3">@lang('saas::app.tenant.billing.paid-at')</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($records as $record)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-800 dark:text-white" dir="ltr">{{ $record->number }}</p>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $record->period_start?->format('Y-m-d') }}
                                &ndash;
                                {{ $record->period_end?->format('Y-m-d') }}
                            </p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $record->status->value }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">
                            {{ number_format((float) $record->total, 2) }} {{ $record->currency }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">
                            {{ number_format($record->balance(), 2) }} {{ $record->currency }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ $record->due_at?->format('Y-m-d') ?? trans('saas::app.common.none') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ $record->paid_at?->format('Y-m-d') ?? trans('saas::app.common.none') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            @lang('saas::app.tenant.billing.empty')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $records->links() }}
    </div>

    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
        @lang('saas::app.tenant.billing.help')
    </p>
</x-admin::layouts>
