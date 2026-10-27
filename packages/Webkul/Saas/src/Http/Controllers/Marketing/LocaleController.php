<?php

namespace Webkul\Saas\Http\Controllers\Marketing;

use Illuminate\Http\Request;
use Webkul\Saas\Http\Middleware\SetMarketingLocale;

class LocaleController extends Controller
{
    /**
     * Switch the language of the public website.
     */
    public function update(Request $request, string $locale)
    {
        abort_unless(in_array($locale, SetMarketingLocale::SUPPORTED, true), 404);

        $request->session()->put('saas_marketing_locale', $locale);

        $redirect = (string) $request->query('redirect', '/');

        if (! str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = '/';
        }

        return redirect($redirect);
    }
}
