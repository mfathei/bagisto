<?php

namespace Webkul\Saas\Http\Controllers\Marketing;

use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Services\MarketingContent;

class MarketingPageController extends Controller
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
     * The public homepage.
     */
    public function home()
    {
        return view('saas::marketing.pages.home', [
            'meta' => $this->meta(
                trans('saas::marketing.meta.home_title'),
                trans('saas::marketing.meta.home_description', ['platform' => $this->content->platformName()])
            ),
            'site' => $this->site(),
            'hero' => $this->content->hero(),
            'features' => $this->featureList(),
            'plans' => $this->plans->publicPlans()->take(3),
            'faqs' => $this->faqs(config('saas.marketing.faqs', [])),
        ]);
    }

    /**
     * The detailed feature overview.
     */
    public function features()
    {
        $groups = collect(config('saas.marketing.feature_groups', []))
            ->map(fn (array $group) => [
                'key' => $group['key'],
                'title' => trans('saas::marketing.features.groups.'.$group['key'].'.title'),
                'description' => trans('saas::marketing.features.groups.'.$group['key'].'.description'),
                'items' => collect($group['features'])
                    ->map(fn (string $key) => $this->feature($key))
                    ->all(),
            ])
            ->all();

        return view('saas::marketing.pages.features', [
            'meta' => $this->meta(
                trans('saas::marketing.meta.features_title'),
                trans('saas::marketing.meta.features_description')
            ),
            'site' => $this->site(),
            'groups' => $groups,
        ]);
    }

    /**
     * Business types the platform can serve.
     */
    public function solutions()
    {
        $solutions = collect(config('saas.marketing.solutions', []))
            ->map(fn (array $solution) => [
                'key' => $solution['key'],
                'icon' => $solution['icon'],
                'title' => trans('saas::marketing.solutions.items.'.$solution['key'].'.title'),
                'description' => trans('saas::marketing.solutions.items.'.$solution['key'].'.description'),
                'points' => trans('saas::marketing.solutions.items.'.$solution['key'].'.points'),
            ])
            ->all();

        return view('saas::marketing.pages.solutions', [
            'meta' => $this->meta(
                trans('saas::marketing.meta.solutions_title'),
                trans('saas::marketing.meta.solutions_description')
            ),
            'site' => $this->site(),
            'solutions' => $solutions,
        ]);
    }

    /**
     * Guides and answers.
     */
    public function resources()
    {
        return view('saas::marketing.pages.resources', [
            'meta' => $this->meta(
                trans('saas::marketing.meta.resources_title'),
                trans('saas::marketing.meta.resources_description')
            ),
            'site' => $this->site(),
            'faqs' => $this->faqs(config('saas.marketing.faqs', [])),
        ]);
    }

    /**
     * Company and platform introduction.
     */
    public function about()
    {
        return view('saas::marketing.pages.about', [
            'meta' => $this->meta(
                trans('saas::marketing.meta.about_title'),
                trans('saas::marketing.meta.about_description', ['platform' => $this->content->platformName()])
            ),
            'site' => $this->site(),
            'aboutIntro' => $this->content->aboutIntro(),
        ]);
    }
}
