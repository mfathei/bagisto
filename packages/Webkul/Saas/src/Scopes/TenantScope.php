<?php

namespace Webkul\Saas\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Webkul\Saas\Services\TenantContext;

class TenantScope implements Scope
{
    /**
     * Apply the tenant constraint when a context is active.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if (! $context->check()) {
            return;
        }

        $builder->where(
            $model->getTable().'.tenant_id',
            $context->id()
        );
    }
}
