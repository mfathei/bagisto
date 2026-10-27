<?php

namespace Webkul\Saas\Services;

use Illuminate\Support\Facades\Crypt;
use Webkul\Saas\Models\PlatformSetting;
use Webkul\Saas\Repositories\PlatformSettingRepository;

class PlatformSettings
{
    /**
     * Request-scoped cache of decoded settings.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $resolved = null;

    /**
     * Create a new settings service.
     */
    public function __construct(protected PlatformSettingRepository $repository) {}

    /**
     * Read a platform setting, falling back to the config default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $default ?? config('saas.settings.'.$key);
    }

    /**
     * Whether a platform setting is enabled.
     */
    public function boolean(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    /**
     * Read an integer platform setting.
     */
    public function integer(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * Persist a platform setting, encrypting values marked as secret.
     */
    public function set(string $key, mixed $value, string $type = 'string', string $group = 'general', bool $encrypted = false): PlatformSetting
    {
        $stored = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value ?? [], JSON_THROW_ON_ERROR),
            default => $value === null ? null : (string) $value,
        };

        if ($encrypted && $stored !== null) {
            $stored = Crypt::encryptString($stored);
        }

        $setting = $this->repository->put($key, $stored, $type, $group, $encrypted);

        $this->resolved = null;

        return $setting;
    }

    /**
     * Persist many settings in one pass.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                is_array($value) => 'json',
                default => 'string',
            };

            $this->set((string) $key, $value, $type, $group);
        }
    }

    /**
     * All stored settings decoded and keyed by name.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $settings = [];

        foreach ($this->repository->all() as $setting) {
            $settings[$setting->key] = $this->decode($setting);
        }

        return $this->resolved = $settings;
    }

    /**
     * Forget the request-scoped cache after external writes.
     */
    public function flush(): void
    {
        $this->resolved = null;
    }

    /**
     * Whether public tenant registration is open.
     */
    public function registrationEnabled(): bool
    {
        return (bool) config('saas.registration.enabled') && $this->boolean('registration_enabled', true);
    }

    /**
     * Whether a Super Admin must approve new tenants.
     */
    public function requiresApproval(): bool
    {
        return $this->boolean('require_approval', (bool) config('saas.registration.require_approval'));
    }

    /**
     * Whether owner email verification is required before activation.
     */
    public function requiresEmailVerification(): bool
    {
        return $this->boolean('require_email_verification', (bool) config('saas.registration.require_email_verification'));
    }

    /**
     * The default trial length in days.
     */
    public function defaultTrialDays(): int
    {
        return max(0, $this->integer('default_trial_days', (int) config('saas.registration.trial_days')));
    }

    /**
     * The configured platform display name.
     */
    public function platformName(): string
    {
        return (string) $this->get('platform_name', config('saas.platform_name'));
    }

    /**
     * The platform support email address.
     */
    public function supportEmail(): string
    {
        return (string) $this->get('support_email', config('saas.settings.support_email'));
    }

    /**
     * The plan assigned when a tenant registers without a choice.
     */
    public function defaultPlanSlug(): string
    {
        return (string) $this->get('default_plan_slug', config('saas.settings.default_plan_slug'));
    }

    /**
     * Decode a stored setting according to its declared type.
     */
    protected function decode(PlatformSetting $setting): mixed
    {
        $value = $setting->value;

        if ($setting->is_encrypted && $value !== null) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Throwable) {
                return null;
            }
        }

        return match ($setting->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'integer' => is_numeric($value) ? (int) $value : 0,
            'json' => $value === null ? [] : json_decode($value, true) ?? [],
            default => $value,
        };
    }
}
