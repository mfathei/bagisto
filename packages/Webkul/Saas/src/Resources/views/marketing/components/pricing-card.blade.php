@props(['plan', 'registrationEnabled' => true, 'featured' => false])

@php
    use Webkul\Saas\Enums\FeatureKey;

    $price = (float) $plan->price;

    $interval = in_array($plan->billing_interval, ['monthly', 'yearly', 'quarterly'], true)
        ? $plan->billing_interval
        : 'monthly';

    $quotas = [
        FeatureKey::MaxProducts->value => $plan->limit(FeatureKey::MaxProducts),
        FeatureKey::MaxStaff->value => $plan->limit(FeatureKey::MaxStaff),
        FeatureKey::MonthlyOrders->value => $plan->limit(FeatureKey::MonthlyOrders),
        FeatureKey::StorageMb->value => $plan->limit(FeatureKey::StorageMb),
    ];

    $entitlements = [
        FeatureKey::CustomDomain->value => $plan->includes(FeatureKey::CustomDomain),
        FeatureKey::AdvancedAnalytics->value => $plan->includes(FeatureKey::AdvancedAnalytics),
        FeatureKey::ApiAccess->value => $plan->includes(FeatureKey::ApiAccess),
        FeatureKey::PremiumSupport->value => $plan->includes(FeatureKey::PremiumSupport),
    ];

    $cta = $registrationEnabled
        ? route('saas.registration.create', ['plan' => $plan->slug], false)
        : route('saas.marketing.contact', [], false);
@endphp

<article @class(['price-card', 'featured' => $featured])>
    @if ($featured)
        <span class="price-badge">@lang('saas::marketing.pricing.recommended')</span>
    @endif

    <h3>{{ $plan->name }}</h3>

    @if ($plan->description)
        <p class="plan-description">{{ $plan->description }}</p>
    @endif

    <div class="price-amount">
        @if ($price <= 0)
            <strong>@lang('saas::marketing.pricing.free')</strong>
        @else
            <strong>{{ number_format($price, 2) }}</strong>
            <span>{{ $plan->currency }} / @lang('saas::marketing.pricing.intervals.'.$interval)</span>
        @endif
    </div>

    @if ((int) $plan->trial_days > 0)
        <span class="trial-note">
            @lang('saas::marketing.pricing.trial', ['days' => (int) $plan->trial_days])
        </span>
    @endif

    <div class="price-lists">
        @if (collect($quotas)->filter(fn ($value) => $value !== null)->isNotEmpty())
            <div>
                <h4>@lang('saas::marketing.pricing.limits')</h4>

                <ul>
                    @foreach ($quotas as $key => $value)
                        <li>
                            <span class="tick"><x-marketing::icon name="check" /></span>
                            <span>{{ trans('saas::app.features.'.$key) }}: {{ $value === null ? trans('saas::marketing.pricing.unlimited') : number_format($value) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <h4>@lang('saas::marketing.pricing.features')</h4>

            <ul>
                @foreach ($entitlements as $key => $included)
                    <li>
                        @if ($included)
                            <span class="tick"><x-marketing::icon name="check" /></span>
                        @else
                            <span class="cross"><x-marketing::icon name="cross" /></span>
                        @endif

                        <span>{{ trans('saas::app.features.'.$key) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <x-marketing::button :href="$cta" :variant="$featured ? 'primary' : 'ghost'">
        {{ $registrationEnabled ? trans('saas::marketing.pricing.choose') : trans('saas::marketing.common.contact_sales') }}
    </x-marketing::button>
</article>
