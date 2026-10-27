<?php

namespace Webkul\Saas\Http\Controllers\Tenant;

use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Services\AuditLogger;
use Webkul\Saas\Services\TenantManager;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TenantManager $manager,
        protected AuditLogger $audit
    ) {}

    /**
     * Show the tenant profile and localization settings.
     */
    public function index()
    {
        $this->authorizeAdministrative();

        return view('saas::tenant.settings', [
            'tenant' => $this->tenant(),
            'locales' => core()->getAllLocales(),
            'currencies' => core()->getAllCurrencies(),
            'timezones' => DateTimeZone::listIdentifiers(),
        ]);
    }

    /**
     * Persist the tenant profile, localization and contact details.
     */
    public function update(Request $request)
    {
        $this->authorizeAdministrative();

        $tenant = $this->tenant();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:40'],
            'timezone' => ['required', 'timezone'],
            'default_locale' => ['required', Rule::in(core()->getAllLocales()->pluck('code')->all())],
            'default_currency' => ['required', Rule::in(core()->getAllCurrencies()->pluck('code')->all())],
            'tagline' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:191'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
        ]);

        $data['settings'] = [
            'tagline' => $data['tagline'] ?? null,
            'contact_email' => $data['contact_email'] ?? $data['email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? $data['phone'] ?? null,
            'timezone' => $data['timezone'],
            'locale' => $data['default_locale'],
            'currency' => $data['default_currency'],
        ];

        $this->manager->update($tenant, $data);

        $this->audit->forTenant($tenant, AuditAction::TenantSettingsUpdated, [
            'description' => 'Tenant settings updated from the tenant panel.',
            'metadata' => ['fields' => array_keys($data)],
        ]);

        return back()->with('success', trans('saas::app.tenant.settings.saved'));
    }
}
