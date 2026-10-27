<?php

namespace Webkul\Saas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Webkul\Saas\Contracts\TenantInvitation;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Enums\FeatureKey;
use Webkul\Saas\Enums\MembershipStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Repositories\PlatformAdminRepository;
use Webkul\Saas\Repositories\TenantInvitationRepository;
use Webkul\Saas\Repositories\TenantUserRepository;
use Webkul\Saas\Services\AuditLogger;
use Webkul\Saas\Services\EntitlementService;
use Webkul\Saas\Services\TenantRoleService;
use Webkul\User\Repositories\AdminRepository;

class InvitationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TenantInvitationRepository $invitations,
        protected TenantUserRepository $memberships,
        protected PlatformAdminRepository $admins,
        protected AdminRepository $adminAccounts,
        protected TenantRoleService $tenantRoles,
        protected EntitlementService $entitlements,
        protected AuditLogger $audit
    ) {}

    /**
     * Show the invitation acceptance form.
     */
    public function show(string $token)
    {
        $invitation = $this->findInvitation($token);

        return view('saas::invitation.show', [
            'token' => $token,
            'invitation' => $invitation,
            'tenant' => $invitation->tenant,
        ]);
    }

    /**
     * Create the invited administrator account and membership.
     */
    public function accept(Request $request, string $token)
    {
        $invitation = $this->findInvitation($token);
        $tenant = $invitation->tenant;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'password' => [
                'required',
                'string',
                'min:'.config('saas.registration.password_min_length', 12),
                'confirmed',
            ],
        ]);

        if ($this->admins->findByEmail($invitation->email)) {
            throw ValidationException::withMessages([
                'email' => trans('saas::app.errors.email-taken'),
            ]);
        }

        $this->entitlements->assertCanCreate($tenant, FeatureKey::MaxStaff);

        $role = $this->tenantRoles->ensure($tenant);

        DB::transaction(function () use ($invitation, $tenant, $role, $data) {
            if (! $this->invitations->markAccepted($invitation)) {
                throw ValidationException::withMessages([
                    'invitation' => trans('saas::app.errors.invitation-inactive'),
                ]);
            }

            $admin = $this->adminAccounts->create([
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
                'role_id' => $role->getKey(),
                'status' => 1,
            ]);

            $admin->forceFill(['tenant_id' => $tenant->getKey()])->save();

            $this->memberships->create([
                'tenant_id' => $tenant->getKey(),
                'admin_id' => $admin->getKey(),
                'role' => $invitation->role->value,
                'status' => MembershipStatus::Active->value,
                'invited_by' => $invitation->invited_by,
                'invited_at' => $invitation->created_at,
                'accepted_at' => now(),
                'email_verified_at' => now(),
            ]);

            $this->audit->forTenant($tenant, AuditAction::UserJoined, [
                'subject' => $admin,
                'description' => 'Invited team member joined.',
                'metadata' => ['role' => $invitation->role->value],
            ]);
        });

        return redirect()
            ->route('admin.session.create')
            ->with('success', trans('saas::app.invitation.joined'));
    }

    /**
     * Resolve a usable invitation, rejecting expired or revoked tokens.
     */
    protected function findInvitation(string $token): TenantInvitation
    {
        $invitation = $this->invitations->findByTokenHash(hash('sha256', $token));

        abort_if(! $invitation, 404);

        abort_unless($invitation->isPending(), 410, trans('saas::app.errors.invitation-inactive'));

        $tenant = $invitation->tenant;

        abort_if(! $tenant || $tenant->status !== TenantStatus::Active, 410, trans('saas::app.errors.store-unavailable'));

        return $invitation;
    }
}
