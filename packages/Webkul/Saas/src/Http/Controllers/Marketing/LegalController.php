<?php

namespace Webkul\Saas\Http\Controllers\Marketing;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Render one of the editable legal templates.
     */
    public function show(Request $request, string $page)
    {
        abort_unless(array_key_exists($page, (array) config('saas.marketing.legal', [])), 404);

        $sections = trans('saas::marketing.legal.'.$page.'.sections');

        return view('saas::marketing.pages.legal', [
            'meta' => $this->meta(trans('saas::marketing.legal.'.$page.'.title')),
            'site' => $this->site(),
            'page' => $page,
            'sections' => is_array($sections) ? $sections : [],
        ]);
    }
}
