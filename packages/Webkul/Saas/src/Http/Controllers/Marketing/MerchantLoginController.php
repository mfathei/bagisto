<?php

namespace Webkul\Saas\Http\Controllers\Marketing;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Services\MarketingContent;
use Webkul\Saas\Services\TenantResolver;

class MerchantLoginController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        MarketingContent $content,
        protected TenantRepository $tenants,
        protected TenantResolver $resolver
    ) {
        parent::__construct($content);
    }

    /**
     * Show the store finder used to reach a tenant admin panel.
     */
    public function show()
    {
        return view('saas::marketing.pages.merchant-login', [
            'meta' => $this->meta(trans('saas::marketing.meta.merchant_login_title')),
            'site' => $this->site(),
        ]);
    }

    /**
     * Resolve the merchant's store and send them to its admin panel.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'store' => ['required', 'string', 'max:191'],
        ]);

        $lookup = $this->normalize($data['store']);

        $tenant = str_contains($lookup, '.')
            ? $this->resolver->resolve($lookup)
            : $this->tenants->findBySlug($lookup);

        if (! $tenant instanceof Tenant || ! $tenant->isServing()) {
            throw ValidationException::withMessages([
                'store' => trans('saas::marketing.merchant_login.not-found'),
            ]);
        }

        $storefront = $tenant->storefrontUrl();

        if (! $storefront) {
            throw ValidationException::withMessages([
                'store' => trans('saas::marketing.merchant_login.not-found'),
            ]);
        }

        return redirect()->away(
            rtrim($storefront, '/').'/'.trim((string) config('app.admin_url'), '/')
        );
    }

    /**
     * Reduce the submitted store reference to a hostname or slug.
     */
    protected function normalize(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('#^[a-z][a-z0-9+.-]*://#i', '', $value) ?? $value;
        $value = explode('/', $value)[0];
        $value = explode('?', $value)[0];
        $value = strtolower(rtrim($value, '.'));

        return preg_replace('/:\d+$/', '', $value) ?? $value;
    }
}
