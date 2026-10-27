@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.plans.title'))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.super-admin.plans.title')</h1>

            <p>@lang('saas::app.super-admin.plans.subtitle')</p>
        </div>

        <a class="btn" href="{{ route('saas.super-admin.plans.create') }}">
            @lang('saas::app.super-admin.plans.new')
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>@lang('saas::app.common.name')</th>
                    <th>@lang('saas::app.super-admin.plans.slug')</th>
                    <th>@lang('saas::app.super-admin.plans.price')</th>
                    <th>@lang('saas::app.super-admin.plans.interval')</th>
                    <th>@lang('saas::app.super-admin.plans.trial-days')</th>
                    <th>@lang('saas::app.super-admin.plans.active')</th>
                    <th>@lang('saas::app.super-admin.plans.public')</th>
                    <th>@lang('saas::app.common.actions')</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($plans as $plan)
                    <tr>
                        <td>
                            <a href="{{ route('saas.super-admin.plans.show', $plan->getKey()) }}">{{ $plan->name }}</a>
                        </td>
                        <td>{{ $plan->slug }}</td>
                        <td>{{ $plan->price }} {{ $plan->currency }}</td>
                        <td>{{ $plan->billing_interval }}</td>
                        <td>{{ $plan->trial_days }}</td>
                        <td>{{ $plan->is_active ? trans('saas::app.common.yes') : trans('saas::app.common.no') }}</td>
                        <td>{{ $plan->is_public ? trans('saas::app.common.yes') : trans('saas::app.common.no') }}</td>
                        <td>
                            <div class="actions">
                                <a class="btn small secondary" href="{{ route('saas.super-admin.plans.edit', $plan->getKey()) }}">
                                    @lang('saas::app.common.edit')
                                </a>

                                <form class="inline-form" method="POST" action="{{ route('saas.super-admin.plans.destroy', $plan->getKey()) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn small danger">@lang('saas::app.common.delete')</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="muted">@lang('saas::app.super-admin.plans.empty')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
