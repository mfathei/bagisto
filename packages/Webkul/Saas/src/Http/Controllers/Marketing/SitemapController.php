<?php

namespace Webkul\Saas\Http\Controllers\Marketing;

class SitemapController extends Controller
{
    /**
     * XML sitemap for public marketing pages only.
     */
    public function sitemap()
    {
        $root = rtrim(url('/'), '/');

        $urls = collect(config('saas.marketing.sitemap', []))
            ->map(fn (array $entry) => [
                'loc' => $root.route($entry['route'], [], false),
                'priority' => $entry['priority'] ?? '0.5',
                'changefreq' => $entry['changefreq'] ?? 'monthly',
            ])
            ->all();

        $xml = view('saas::marketing.pages.sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * Crawl directives that keep private platform screens out of search results.
     */
    public function robots()
    {
        $root = rtrim(url('/'), '/');

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /super-admin',
            'Disallow: /saas/register/pending',
            'Disallow: /saas/invitation',
            'Disallow: /'.trim((string) config('app.admin_url'), '/'),
            'Disallow: /api',
            '',
            'Sitemap: '.$root.'/sitemap.xml',
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
