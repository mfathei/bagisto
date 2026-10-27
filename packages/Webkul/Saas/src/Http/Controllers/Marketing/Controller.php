<?php

namespace Webkul\Saas\Http\Controllers\Marketing;

use Illuminate\Routing\Controller as BaseController;
use Webkul\Saas\Services\MarketingContent;

abstract class Controller extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected MarketingContent $content) {}

    /**
     * Build the page metadata consumed by the public layout.
     *
     * @return array{title: string, description: string, canonical: string, platform: string, image: ?string}
     */
    protected function meta(string $title, ?string $description = null): array
    {
        $platform = $this->content->platformName();

        return [
            'title' => trim($title) === '' ? $platform : $title.' — '.$platform,
            'description' => $description ?: trans('saas::marketing.meta.description', ['platform' => $platform]),
            'canonical' => url()->current(),
            'platform' => $platform,
            'image' => null,
        ];
    }

    /**
     * Values shared by every public page.
     *
     * @return array<string, mixed>
     */
    protected function site(): array
    {
        return [
            'platform_name' => $this->content->platformName(),
            'brand_color' => $this->content->brandColor(),
            'banner' => $this->content->banner(),
            'contact_email' => $this->content->contactEmail(),
            'contact_phone' => $this->content->contactPhone(),
            'social' => $this->content->socialLinks(),
            'registration_enabled' => $this->content->registrationEnabled(),
        ];
    }

    /**
     * Resolve a feature entry with translated copy.
     *
     * @return array<string, string>
     */
    protected function feature(string $key): array
    {
        $icons = collect(config('saas.marketing.features', []))->keyBy('key');

        return [
            'key' => $key,
            'icon' => (string) ($icons[$key]['icon'] ?? 'check'),
            'title' => trans('saas::marketing.features.items.'.$key.'.title'),
            'description' => trans('saas::marketing.features.items.'.$key.'.description'),
            'benefit' => trans('saas::marketing.features.items.'.$key.'.benefit'),
        ];
    }

    /**
     * All public features in presentation order.
     *
     * @return array<int, array<string, string>>
     */
    protected function featureList(): array
    {
        return collect(config('saas.marketing.features', []))
            ->map(fn (array $feature) => $this->feature($feature['key']))
            ->all();
    }

    /**
     * Resolve a list of FAQ entries from translation keys.
     *
     * @param  string[]  $keys
     * @return array<int, array{question: string, answer: string}>
     */
    protected function faqs(array $keys, string $section = 'faqs'): array
    {
        return collect($keys)->map(fn (string $key) => [
            'question' => trans('saas::marketing.'.$section.'.'.$key.'.question'),
            'answer' => trans('saas::marketing.'.$section.'.'.$key.'.answer'),
        ])->all();
    }

    /**
     * Generate a path on the current platform host.
     *
     * Marketing routes exist once per configured host, so absolute URLs are
     * never generated from a route name; the request host always wins.
     */
    protected function path(string $name, array $parameters = []): string
    {
        return route($name, $parameters, false);
    }
}
