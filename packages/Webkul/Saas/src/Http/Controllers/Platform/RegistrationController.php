<?php

namespace Webkul\Saas\Http\Controllers\Platform;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Mail\TenantVerificationMail;
use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Services\PlatformSettings;
use Webkul\Saas\Services\TenantManager;
use Webkul\User\Repositories\AdminRepository;

class RegistrationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TenantManager $manager,
        protected TenantRepository $tenants,
        protected PlanRepository $plans,
        protected PlatformSettings $settings,
        protected AdminRepository $admins
    ) {}

    /**
     * Show the public registration form.
     */
    public function create()
    {
        abort_unless($this->settings->registrationEnabled(), 404);

        $plans = $this->plans->publicPlans();

        abort_if($plans->isEmpty(), 404);

        $selected = $plans->firstWhere('slug', (string) request('plan')) ?? $plans->first();

        return view('saas::platform.register', [
            'plans' => $plans,
            'selectedPlanId' => $selected?->getKey(),
            'platformName' => $this->settings->platformName(),
            'trialDays' => $this->settings->defaultTrialDays(),
        ]);
    }

    /**
     * Create a pending tenant from a public registration.
     */
    public function store(Request $request)
    {
        abort_unless($this->settings->registrationEnabled(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:30', 'regex:'.config('saas.tenants.slug_regex')],
            'owner_name' => ['required', 'string', 'max:150'],
            'owner_email' => ['required', 'email', 'max:191'],
            'owner_password' => ['required', 'string', 'min:'.config('saas.registration.password_min_length', 12), 'confirmed'],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:40'],
            'plan_id' => ['required', 'integer', 'exists:saas_plans,id'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:12'],
            'currency' => ['nullable', 'string', 'size:3'],
            'terms' => ['accepted'],
        ]);

        $plan = $this->plans->find($data['plan_id']);

        if (! $plan || ! $plan->is_active || ! $plan->is_public) {
            throw ValidationException::withMessages([
                'plan_id' => trans('saas::marketing.pricing.unavailable'),
            ]);
        }

        $result = $this->manager->create(array_merge($data, [
            'email' => $data['email'] ?? $data['owner_email'],
        ]), true);

        $tenant = $result['tenant'];

        $request->session()->put('saas_pending_tenant', $tenant->uuid);

        if ($this->settings->requiresEmailVerification()) {
            $this->sendVerification($tenant);
        } else {
            $tenant = $this->manager->markEmailVerified($tenant);
        }

        if (! $this->settings->requiresApproval()) {
            $this->manager->approve($tenant);
        }

        return redirect()->route('saas.registration.pending');
    }

    /**
     * Show the status of the registration held in the session.
     */
    public function pending(Request $request)
    {
        $uuid = $request->session()->get('saas_pending_tenant');

        if (! $uuid) {
            return redirect()->route('saas.registration.create');
        }

        $tenant = $this->tenants->findByUuid($uuid);

        if (! $tenant) {
            $request->session()->forget('saas_pending_tenant');

            return redirect()->route('saas.registration.create');
        }

        return view('saas::platform.pending', [
            'tenant' => $tenant,
            'requiresApproval' => $this->settings->requiresApproval(),
            'requiresVerification' => $this->settings->requiresEmailVerification(),
            'verificationEmail' => $this->verificationRecipient($tenant),
        ]);
    }

    /**
     * Handle the signed email verification link.
     */
    public function verify(Request $request, string $tenant)
    {
        $record = $this->tenants->findByUuid($tenant);

        abort_if(! $record, 404);

        $record = $this->manager->markEmailVerified($record);

        $request->session()->put('saas_pending_tenant', $record->uuid);

        return redirect()
            ->route('saas.registration.pending')
            ->with('success', trans('saas::app.registration.email-verified'));
    }

    /**
     * Resend the verification link with an enumeration-safe response.
     */
    public function resend(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:191'],
        ]);

        $email = strtolower($data['email']);

        $tenant = $this->tenantForOwnerEmail($email)
            ?? $this->tenants->where('email', $email)->whereNull('email_verified_at')->first();

        if ($tenant && ! $tenant->email_verified_at && $tenant->status !== TenantStatus::Cancelled) {
            $this->sendVerification($tenant);
        }

        return back()->with('success', trans('saas::app.registration.resend-sent'));
    }

    /**
     * Find the tenant whose owner account uses the address.
     */
    protected function tenantForOwnerEmail(string $email): ?Tenant
    {
        $owner = $this->admins->findOneByField('email', $email);

        if (! $owner || ! $owner->tenant_id) {
            return null;
        }

        return $this->tenants->find($owner->tenant_id);
    }

    /**
     * Resolve the address that receives the verification link.
     */
    protected function verificationRecipient(Tenant $tenant): ?string
    {
        if ($tenant->owner_admin_id) {
            $owner = $this->admins->find($tenant->owner_admin_id);

            if ($owner?->email) {
                return $owner->email;
            }
        }

        return $tenant->email;
    }

    /**
     * Send the signed verification mail, logging rather than failing registration.
     */
    protected function sendVerification(Tenant $tenant): void
    {
        $recipient = $this->verificationRecipient($tenant);

        if (! $recipient) {
            return;
        }

        try {
            Mail::to($recipient)->send(
                new TenantVerificationMail($tenant, $this->manager->verificationUrl($tenant))
            );
        } catch (\Throwable $exception) {
            Log::warning('SaaS verification mail could not be sent.', [
                'tenant' => $tenant->uuid,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
