<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Support\Facades\Route;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\ContactInquiryRepository;

class CmsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ContactInquiryRepository $inquiries
    ) {}

    /**
     * List the public marketing pages and recent contact enquiries.
     */
    public function index()
    {
        return view('saas::super-admin.cms.index', [
            'pages' => $this->pages(),
            'openInquiries' => $this->inquiries->openCount(),
            'recentInquiries' => $this->inquiries->getModel()
                ->newQuery()
                ->orderByDesc('created_at')
                ->limit(6)
                ->get(),
        ]);
    }

    /**
     * The published marketing pages, labelled for the CMS listing.
     *
     * @return array<int, array{title: string, url: string, path: string, group: string}>
     */
    protected function pages(): array
    {
        $labels = [
            'saas.marketing.home' => 'Home',
            'saas.marketing.features' => 'Features',
            'saas.marketing.solutions' => 'Solutions',
            'saas.marketing.pricing' => 'Pricing',
            'saas.marketing.resources' => 'Resources',
            'saas.marketing.about' => 'About',
            'saas.marketing.contact' => 'Contact',
            'saas.marketing.privacy' => 'Privacy policy',
            'saas.marketing.terms' => 'Terms of service',
            'saas.marketing.refund-policy' => 'Refund policy',
        ];

        $groups = [
            'saas.marketing.home' => 'Website',
            'saas.marketing.features' => 'Website',
            'saas.marketing.solutions' => 'Website',
            'saas.marketing.pricing' => 'Website',
            'saas.marketing.resources' => 'Website',
            'saas.marketing.about' => 'Website',
            'saas.marketing.contact' => 'Website',
            'saas.marketing.privacy' => 'Legal',
            'saas.marketing.terms' => 'Legal',
            'saas.marketing.refund-policy' => 'Legal',
        ];

        $pages = [];

        foreach (config('saas.marketing.sitemap', []) as $entry) {
            $name = $entry['route'] ?? null;

            if (! $name || ! Route::has($name)) {
                continue;
            }

            $pages[] = [
                'title' => $labels[$name] ?? str($name)->afterLast('.')->headline()->toString(),
                'url' => route($name),
                'path' => parse_url(route($name), PHP_URL_PATH) ?: '/',
                'group' => $groups[$name] ?? 'Website',
            ];
        }

        return $pages;
    }
}
