<?php

namespace Webkul\Saas\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Services\AuditLogger;

class OnboardingController extends Controller
{
    /**
     * The manual checklist steps a tenant can mark as done.
     *
     * @var string[]
     */
    protected array $steps = [
        'profile_completed',
        'branding_completed',
        'payment_configured',
        'shipping_configured',
        'published',
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(protected AuditLogger $audit) {}

    /**
     * Persist the onboarding checklist of the tenant.
     */
    public function update(Request $request)
    {
        $this->authorizeAdministrative();

        $tenant = $this->tenant();

        $rules = [];

        foreach ($this->steps as $step) {
            $rules[$step] = ['sometimes', 'boolean'];
        }

        $data = $request->validate($rules);

        $settings = (array) $tenant->settings;

        $onboarding = (array) ($settings['onboarding'] ?? []);

        foreach ($this->steps as $step) {
            if (array_key_exists($step, $data)) {
                $onboarding[$step] = (bool) $data[$step];
            }
        }

        $settings['onboarding'] = $onboarding;

        $tenant->forceFill(['settings' => $settings])->save();

        $this->audit->forTenant($tenant, AuditAction::TenantSettingsUpdated, [
            'description' => 'Onboarding checklist updated.',
            'metadata' => ['steps' => $onboarding],
        ]);

        return back()->with('success', trans('saas::app.tenant.onboarding.saved'));
    }
}
