<?php

namespace Webkul\Saas\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Contracts\TenantInvitation;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Enums\FeatureKey;
use Webkul\Saas\Enums\MembershipRole;
use Webkul\Saas\Enums\MembershipStatus;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Mail\InvitationMail;
use Webkul\Saas\Repositories\PlatformAdminRepository;
use Webkul\Saas\Repositories\TenantInvitationRepository;
use Webkul\Saas\Repositories\TenantUserRepository;
use Webkul\Saas\Services\AuditLogger;
use Webkul\Saas\Services\EntitlementService;

class TeamController extends Controller
{
    /**
     * How long an invitation stays valid, in days.
     */
    protected const INVITATION_TTL_DAYS = 7;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TenantUserRepository $memberships,
        protected TenantInvitationRepository $invitations,
        protected PlatformAdminRepository $admins,
        protected EntitlementService $entitlements,
        protected AuditLogger $audit
    ) {}

    /**
     * List the tenant staff and pending invitations.
     */
    public function index()
    {
        $this->authorizeAdministrative();

        $tenant = $this->tenant();

        return view('saas::tenant.team', [
            'tenant' => $tenant,
            'members' => $this->memberships
                ->with('admin')
                ->where('tenant_id', $tenant->getKey())
                ->orderBy('id')
                ->get(),
            'invitations' => $this->invitations->pendingForTenant($tenant->getKey()),
            'roles' => array_values(array_filter(
                MembershipRole::cases(),
                fn (MembershipRole $role) => $role !== MembershipRole::Owner
            )),
            'limit' => $this->entitlements->limit($tenant, FeatureKey::MaxStaff),
            'used' => $this->memberships->staffCount($tenant->getKey()),
        ]);
    }

    /**
     * Invite a new staff member to the tenant.
     */
    public function store(Request $request)
    {
        $this->authorizeAdministrative();

        $tenant = $this->tenant();

        $data = $request->validate([
            'email' => ['required', 'email', 'max:191'],
            'role' => ['required', Rule::in(MembershipRole::assignable())],
        ]);

        $email = strtolower($data['email']);

        if ($this->invitations->pendingForEmail($tenant->getKey(), $email)) {
            throw ValidationException::withMessages([
                'email' => trans('saas::app.errors.invitation-pending'),
            ]);
        }

        if ($this->admins->findByEmail($email)) {
            throw ValidationException::withMessages([
                'email' => trans('saas::app.errors.email-taken'),
            ]);
        }

        $pending = $this->invitations->pendingForTenant($tenant->getKey())->count();

        $this->entitlements->assertCanCreate($tenant, FeatureKey::MaxStaff, 1 + $pending);

        $token = $this->newToken();

        $invitation = $this->invitations->create([
            'tenant_id' => $tenant->getKey(),
            'email' => $email,
            'role' => $data['role'],
            'status' => 'pending',
            'token_hash' => hash('sha256', $token),
            'invited_by' => $this->admin()?->getKey(),
            'expires_at' => now()->addDays(self::INVITATION_TTL_DAYS),
        ]);

        $this->sendInvitation($invitation, $tenant, $token);

        $this->audit->forTenant($tenant, AuditAction::UserInvited, [
            'subject' => $invitation,
            'description' => 'Team invitation sent.',
            'metadata' => ['email' => $email, 'role' => $data['role']],
        ]);

        return back()->with('success', trans('saas::app.tenant.team.invited'));
    }

    /**
     * Change the tenant role of an existing member.
     */
    public function update(Request $request, int $membership)
    {
        $this->authorizeAdministrative();

        $tenant = $this->tenant();
        $record = $this->memberships->findOrFail($membership);

        abort_unless((int) $record->tenant_id === (int) $tenant->getKey(), 404);

        if ($record->role === MembershipRole::Owner) {
            throw ValidationException::withMessages([
                'role' => trans('saas::app.errors.owner-role-locked'),
            ]);
        }

        $data = $request->validate([
            'role' => ['required', Rule::in(MembershipRole::assignable())],
        ]);

        $record->forceFill(['role' => $data['role']])->save();

        $this->audit->forTenant($tenant, AuditAction::UserRoleChanged, [
            'subject' => $record,
            'description' => 'Team member role changed.',
            'metadata' => ['admin_id' => $record->admin_id, 'role' => $data['role']],
        ]);

        return back()->with('success', trans('saas::app.tenant.team.updated'));
    }

    /**
     * Suspend a member and revoke their tenant access.
     */
    public function destroy(int $membership)
    {
        $this->authorizeAdministrative();

        $tenant = $this->tenant();
        $record = $this->memberships->findOrFail($membership);

        abort_unless((int) $record->tenant_id === (int) $tenant->getKey(), 404);

        if ($record->role === MembershipRole::Owner || (int) $record->admin_id === (int) $tenant->owner_admin_id) {
            throw ValidationException::withMessages([
                'membership' => trans('saas::app.errors.owner-cannot-be-removed'),
            ]);
        }

        if ((int) $record->admin_id === (int) $this->admin()?->getKey()) {
            throw ValidationException::withMessages([
                'membership' => trans('saas::app.errors.cannot-remove-self'),
            ]);
        }

        $record->forceFill([
            'status' => MembershipStatus::Suspended->value,
        ])->save();

        $record->admin?->forceFill(['status' => 0])->save();

        $this->audit->forTenant($tenant, AuditAction::UserRemoved, [
            'subject' => $record,
            'description' => 'Team member access revoked.',
            'metadata' => ['admin_id' => $record->admin_id],
        ]);

        return back()->with('success', trans('saas::app.tenant.team.removed'));
    }

    /**
     * Rotate the token of a pending invitation and resend it.
     */
    public function resend(int $invitation)
    {
        $this->authorizeAdministrative();

        $tenant = $this->tenant();
        $record = $this->invitations->findOrFail($invitation);

        abort_unless((int) $record->tenant_id === (int) $tenant->getKey(), 404);

        if (! $record->isPending()) {
            throw ValidationException::withMessages([
                'invitation' => trans('saas::app.errors.invitation-inactive'),
            ]);
        }

        $token = $this->newToken();

        $record->forceFill([
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(self::INVITATION_TTL_DAYS),
        ])->save();

        $this->sendInvitation($record, $tenant, $token);

        $this->audit->forTenant($tenant, AuditAction::UserInvited, [
            'subject' => $record,
            'description' => 'Team invitation resent.',
            'metadata' => ['email' => $record->email, 'resent' => true],
        ]);

        return back()->with('success', trans('saas::app.tenant.team.resent'));
    }

    /**
     * Send the invitation mail, logging rather than failing the request.
     */
    protected function sendInvitation(TenantInvitation $invitation, Tenant $tenant, string $token): void
    {
        $storefront = $tenant->storefrontUrl();

        $acceptUrl = $storefront
            ? rtrim($storefront, '/').'/saas/invitation/'.rawurlencode($token)
            : route('saas.invitation.show', ['token' => $token]);

        try {
            Mail::to($invitation->email)->send(new InvitationMail(
                $tenant,
                $invitation,
                $acceptUrl,
                (string) ($this->admin()?->name ?? $tenant->name)
            ));
        } catch (\Throwable $exception) {
            Log::warning('SaaS invitation mail could not be sent.', [
                'tenant' => $tenant->uuid,
                'invitation' => $invitation->uuid,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Generate an unguessable invitation token.
     */
    protected function newToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
    }
}
