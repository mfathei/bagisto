<x-admin::layouts>
    <x-slot:title>
        @lang('saas::app.tenant.team.title')
    </x-slot>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('saas::app.tenant.team.title')
            </p>

            <p class="text-gray-600 dark:text-gray-300">
                @lang('saas::app.tenant.team.subtitle')

                &middot;

                @lang('saas::app.tenant.team.quota', [
                    'used' => $used,
                    'limit' => $limit === null ? trans('saas::app.common.unlimited') : $limit,
                ])
            </p>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-4 max-xl:flex-col">
        <div class="flex flex-1 flex-col gap-4">
            <div class="box-shadow overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <table class="w-full text-left">
                    <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">@lang('saas::app.tenant.team.member')</th>
                            <th class="px-4 py-3">@lang('saas::app.tenant.team.role')</th>
                            <th class="px-4 py-3">@lang('saas::app.common.status')</th>
                            <th class="px-4 py-3">@lang('saas::app.tenant.team.joined')</th>
                            <th class="px-4 py-3">@lang('saas::app.common.actions')</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($members as $member)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-800 dark:text-white">
                                        {{ $member->admin?->name ?? trans('saas::app.common.none') }}
                                    </p>

                                    <p class="text-xs text-gray-500 dark:text-gray-400" dir="ltr">
                                        {{ $member->admin?->email }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($member->role === \Webkul\Saas\Enums\MembershipRole::Owner)
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                            {{ $member->role->label() }}
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('saas.tenant.team.update', $member->getKey()) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')

                                            <select name="role" class="rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->value }}" @selected($member->role === $role)>
                                                        {{ $role->label() }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <button type="submit" class="secondary-button text-xs">
                                                @lang('saas::app.common.save')
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $member->status === \Webkul\Saas\Enums\MembershipStatus::Active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $member->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $member->accepted_at?->format('Y-m-d') ?? $member->created_at?->format('Y-m-d') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($member->role !== \Webkul\Saas\Enums\MembershipRole::Owner && (int) $member->admin_id !== (int) auth()->guard('admin')->id())
                                        <form method="POST" action="{{ route('saas.tenant.team.destroy', $member->getKey()) }}" onsubmit="return confirm('@lang('saas::app.tenant.team.confirm-remove')');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="text-sm font-semibold text-red-600">
                                                @lang('saas::app.tenant.team.remove')
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-sm text-gray-400">@lang('saas::app.common.none')</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    @lang('saas::app.tenant.team.no-members')
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('saas::app.tenant.team.pending-invitations')
                </p>

                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="py-2">@lang('saas::app.tenant.team.email')</th>
                                <th class="py-2">@lang('saas::app.tenant.team.role')</th>
                                <th class="py-2">@lang('saas::app.tenant.team.expires')</th>
                                <th class="py-2">@lang('saas::app.common.actions')</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($invitations as $invitation)
                                <tr>
                                    <td class="py-2.5 text-sm text-gray-800 dark:text-white" dir="ltr">{{ $invitation->email }}</td>
                                    <td class="py-2.5 text-sm text-gray-600 dark:text-gray-300">{{ $invitation->role->label() }}</td>
                                    <td class="py-2.5 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $invitation->expires_at?->format('Y-m-d') }}
                                    </td>
                                    <td class="py-2.5">
                                        <form method="POST" action="{{ route('saas.tenant.team.resend', $invitation->getKey()) }}">
                                            @csrf

                                            <button type="submit" class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                                @lang('saas::app.tenant.team.resend')
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        @lang('saas::app.tenant.team.no-invitations')
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="w-full xl:w-96">
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('saas::app.tenant.team.invite')
                </p>

                <form method="POST" action="{{ route('saas.tenant.team.store') }}" class="mt-3 grid gap-3">
                    @csrf

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="email">
                            @lang('saas::app.tenant.team.email')
                        </label>

                        <input id="email" type="email" name="email" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" maxlength="191" value="{{ old('email') }}" required>

                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white" for="role">
                            @lang('saas::app.tenant.team.role')
                        </label>

                        <select id="role" name="role" class="w-full rounded-md border border-gray-200 px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-white" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected(old('role') === $role->value)>
                                    {{ $role->label() }}
                                </option>
                            @endforeach
                        </select>

                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @error('membership')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="primary-button">
                        @lang('saas::app.tenant.team.send-invitation')
                    </button>
                </form>

                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    @lang('saas::app.tenant.team.invite-help')
                </p>
            </div>
        </div>
    </div>
</x-admin::layouts>
