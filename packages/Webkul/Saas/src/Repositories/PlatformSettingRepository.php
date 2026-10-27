<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\PlatformSetting;

class PlatformSettingRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PlatformSetting::class;
    }

    /**
     * Raw stored value of a platform setting.
     */
    public function raw(string $key): ?PlatformSetting
    {
        return $this->model->where('key', $key)->first();
    }

    /**
     * Store a platform setting.
     */
    public function put(string $key, ?string $value, string $type = 'string', string $group = 'general', bool $encrypted = false): PlatformSetting
    {
        return $this->model->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'is_encrypted' => $encrypted,
            ]
        );
    }
}
