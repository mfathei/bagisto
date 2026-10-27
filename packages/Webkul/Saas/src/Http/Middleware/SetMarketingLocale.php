<?php

namespace Webkul\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetMarketingLocale
{
    /**
     * Locales the public website is translated into.
     *
     * @var string[]
     */
    public const SUPPORTED = ['en', 'ar'];

    /**
     * Apply the visitor's marketing locale without touching storefront locale state.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->session()->get('saas_marketing_locale', config('app.locale'));

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = in_array(config('app.locale'), self::SUPPORTED, true) ? (string) config('app.locale') : 'en';
        }

        app()->setLocale($locale);

        View::share('marketingLocale', $locale);
        View::share('marketingDirection', $locale === 'ar' ? 'rtl' : 'ltr');

        return $next($request);
    }
}
