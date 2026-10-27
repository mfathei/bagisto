<x-admin::layouts>
    <x-slot:title>
        @lang('saas::app.tenant.usage.title')
    </x-slot>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('saas::app.tenant.usage.title')
            </p>

            <p class="text-gray-600 dark:text-gray-300">
                @lang('saas::app.tenant.usage.subtitle', ['plan' => $tenant->plan?->name ?? trans('saas::app.common.none')])
            </p>
        </div>
    </div>

    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.tenant.usage.metrics.products')</p>

            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">
                {{ number_format((float) ($snapshot['products'] ?? 0), 0) }}
            </p>
        </div>

        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.tenant.usage.metrics.orders')</p>

            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">
                {{ number_format((float) ($snapshot['orders'] ?? 0), 0) }}
            </p>
        </div>

        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.tenant.usage.metrics.customers')</p>

            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">
                {{ number_format((float) ($snapshot['customers'] ?? 0), 0) }}
            </p>
        </div>

        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.tenant.usage.metrics.storage_mb')</p>

            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">
                {{ number_format((float) ($snapshot['storage_mb'] ?? 0), 2) }} MB
            </p>
        </div>
    </div>

    <div class="mt-4 box-shadow overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="w-full text-left">
            <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3">@lang('saas::app.tenant.usage.feature')</th>
                    <th class="px-4 py-3">@lang('saas::app.tenant.usage.used')</th>
                    <th class="px-4 py-3">@lang('saas::app.tenant.usage.limit')</th>
                    <th class="px-4 py-3">@lang('saas::app.tenant.usage.remaining')</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach ($summary as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">
                            @lang('saas::app.features.'.$row['key'])
                        </td>

                        @if ($row['type'] === 'quota')
                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">
                                {{ number_format((float) $row['used'], 0) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $row['limit'] === null ? trans('saas::app.common.unlimited') : number_format((float) $row['limit'], 0) }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($row['limit'] === null)
                                    <span class="text-sm text-gray-600 dark:text-gray-300">@lang('saas::app.common.unlimited')</span>
                                @else
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-32 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                                            <div
                                                class="h-full {{ $row['over'] ? 'bg-red-500' : 'bg-blue-500' }}"
                                                style="width: {{ min(100, (float) ($row['percent'] ?? 0)) }}%"
                                            ></div>
                                        </div>

                                        <span class="text-xs font-semibold {{ $row['over'] ? 'text-red-600' : 'text-gray-600 dark:text-gray-300' }}">
                                            {{ number_format((float) $row['remaining'], 0) }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                        @else
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $row['included'] ? trans('saas::app.common.included') : trans('saas::app.common.not-included') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ trans('saas::app.common.none') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ trans('saas::app.common.none') }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (collect($summary)->where('over', true)->isNotEmpty())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            @lang('saas::app.tenant.usage.over-quota')
        </div>
    @endif

    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
        @lang('saas::app.tenant.usage.help')
    </p>
</x-admin::layouts>
