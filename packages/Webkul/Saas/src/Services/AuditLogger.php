<?php

namespace Webkul\Saas\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Webkul\Saas\Contracts\AuditLog;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Repositories\AuditLogRepository;

class AuditLogger
{
    /**
     * Metadata keys that must never reach the audit trail.
     *
     * @var string[]
     */
    protected array $redactedKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'secret',
        'api_key',
        'api_secret',
        'token',
        'authorization',
        'card',
        'card_number',
        'cvv',
        'cvc',
        'private_key',
        'client_secret',
        'verification_token',
        'token_hash',
    ];

    /**
     * Create a new audit logger.
     */
    public function __construct(protected AuditLogRepository $repository) {}

    /**
     * Record a platform or tenant event with the resolved actor.
     *
     * @param  array{description?: string, subject?: Model|null, metadata?: array<string, mixed>}  $options
     */
    public function log(AuditAction|string $action, ?Tenant $tenant = null, array $options = []): AuditLog
    {
        $actor = $this->resolveActor();

        return $this->repository->create([
            'tenant_id' => $tenant?->getKey(),
            'actor_type' => $actor['type'],
            'actor_id' => $actor['id'],
            'actor_name' => $actor['name'],
            'action' => $action instanceof AuditAction ? $action->value : $action,
            'subject_type' => isset($options['subject']) ? $options['subject']::class : null,
            'subject_id' => isset($options['subject']) ? $options['subject']->getKey() : null,
            'description' => isset($options['description']) ? mb_substr($options['description'], 0, 1000) : null,
            'metadata' => $this->redact($options['metadata'] ?? []),
            'ip_address' => $this->requestIp(),
            'user_agent' => $this->userAgent(),
        ]);
    }

    /**
     * Record an event explicitly attributed to a tenant.
     *
     * @param  array{description?: string, subject?: Model|null, metadata?: array<string, mixed>}  $options
     */
    public function forTenant(Tenant $tenant, AuditAction|string $action, array $options = []): AuditLog
    {
        return $this->log($action, $tenant, $options);
    }

    /**
     * Attribute an action to the platform rather than a tenant.
     *
     * @param  array{description?: string, subject?: Model|null, metadata?: array<string, mixed>}  $options
     */
    public function platform(AuditAction|string $action, array $options = []): AuditLog
    {
        return $this->log($action, null, $options);
    }

    /**
     * Record a background event with no authenticated actor.
     *
     * @param  array{description?: string, subject?: Model|null, metadata?: array<string, mixed>}  $options
     */
    public function system(AuditAction|string $action, ?Tenant $tenant = null, array $options = []): AuditLog
    {
        $options['metadata'] = array_merge($options['metadata'] ?? [], ['system' => true]);

        return $this->log($action, $tenant, $options);
    }

    /**
     * Resolve the authenticated actor without trusting request input.
     *
     * @return array{type: string, id: int|null, name: string|null}
     */
    protected function resolveActor(): array
    {
        $superAdmin = Auth::guard('super_admin')->user();

        if ($superAdmin) {
            return ['type' => 'super_admin', 'id' => $superAdmin->getKey(), 'name' => $superAdmin->name];
        }

        $admin = Auth::guard('admin')->user();

        if ($admin) {
            return ['type' => 'admin', 'id' => $admin->getKey(), 'name' => $admin->name];
        }

        return ['type' => 'system', 'id' => null, 'name' => 'System'];
    }

    /**
     * Remove credentials and secrets from audit metadata.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    protected function redact(array $metadata): array
    {
        $clean = [];

        foreach ($metadata as $key => $value) {
            if (in_array(strtolower((string) $key), $this->redactedKeys, true)) {
                continue;
            }

            $clean[$key] = is_array($value) ? $this->redact($value) : $value;
        }

        return $clean;
    }

    /**
     * The client address when running an HTTP request.
     */
    protected function requestIp(): ?string
    {
        return app()->runningInConsole() ? null : request()->ip();
    }

    /**
     * The client user agent when running an HTTP request.
     */
    protected function userAgent(): ?string
    {
        return app()->runningInConsole() ? null : mb_substr((string) request()->userAgent(), 0, 500);
    }
}
