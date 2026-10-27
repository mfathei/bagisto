<?php

namespace Webkul\Saas\Http\Controllers\Marketing;

use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Services\MarketingContent;

class PricingController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        MarketingContent $content,
        protected PlanRepository $plans
    ) {
        parent::__construct($content);
    }

    /**
     * Public plan catalogue sourced from the SaaS plan table.
     */
    public function index()
    {
        return view('saas::marketing.pages.pricing', [
            'meta' => $this->meta(
                trans('saas::marketing.meta.pricing_title'),
                trans('saas::marketing.meta.pricing_description')
            ),
            'site' => $this->site(),
            'plans' => $this->plans->publicPlans(),
            'faqs' => $this->faqs(config('saas.marketing.pricing_faqs', []), 'pricing_faqs'),
        ]);
    }
}
