<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Services\AuditLogger;
use Webkul\Saas\Services\PlatformSettings;

class SettingsController extends Controller
{
    /**
     * Setting keys with their storage types.
     *
     * @var array<string, string>
     */
    protected array $keys = [
        'platform_name' => 'string',
        'support_email' => 'string',
        'registration_enabled' => 'boolean',
        'require_approval' => 'boolean',
        'require_email_verification' => 'boolean',
        'default_trial_days' => 'integer',
        'default_plan_slug' => 'string',
        'maintenance_message' => 'string',
        'data_retention_days' => 'integer',
        'default_product_limit' => 'integer',
        'default_staff_limit' => 'integer',
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PlatformSettings $settings,
        protected AuditLogger $audit
    ) {}

    /**
     * Show the platform settings form.
     */
    public function index()
    {
        return view('saas::super-admin.settings.index', [
            'settings' => $this->settings->all(),
            'defaults' => config('saas.settings'),
        ]);
    }

    /**
     * Persist the platform settings.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'platform_name' => ['required', 'string', 'max:120'],
            'support_email' => ['required', 'email', 'max:191'],
            'registration_enabled' => ['nullable', 'boolean'],
            'require_approval' => ['nullable', 'boolean'],
            'require_email_verification' => ['nullable', 'boolean'],
            'default_trial_days' => ['required', 'integer', 'min:0', 'max:365'],
            'default_plan_slug' => ['required', 'string', 'max:60'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'data_retention_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'default_product_limit' => ['required', 'integer', 'min:0', 'max:1000000'],
            'default_staff_limit' => ['required', 'integer', 'min:0', 'max:10000'],
        ]);

        foreach ($this->keys as $key => $type) {
            if (! array_key_exists($key, $data)) {
                if ($type !== 'boolean') {
                    continue;
                }

                $data[$key] = false;
            }

            $value = $type === 'boolean'
                ? (bool) $data[$key]
                : $data[$key];

            $this->settings->set($key, $value, $type, 'platform');
        }

        $this->settings->flush();

        $this->audit->platform(AuditAction::SettingUpdated, [
            'description' => 'Platform settings updated.',
            'metadata' => ['keys' => array_keys($data)],
        ]);

        return back()->with('success', trans('saas::app.super-admin.settings.saved'));
    }
}
