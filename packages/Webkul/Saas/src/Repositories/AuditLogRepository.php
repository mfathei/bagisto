<?php

namespace Webkul\Saas\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\AuditLog;

class AuditLogRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return AuditLog::class;
    }

    /**
     * Filtered platform audit listing.
     */
    public function paginateFiltered(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return $this->model
            ->with('tenant')
            ->when(! empty($filters['tenant_id']), fn ($query) => $query->where('tenant_id', $filters['tenant_id']))
            ->when(! empty($filters['action']), fn ($query) => $query->where('action', $filters['action']))
            ->when(
                ! empty($filters['search']),
                fn ($query) => $query->where(function ($query) use ($filters) {
                    $term = '%'.$filters['search'].'%';

                    $query->where('actor_name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('action', 'like', $term);
                })
            )
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Recent events for dashboards and tenant detail pages.
     */
    public function recent(int $limit = 10, ?int $tenantId = null)
    {
        return $this->model
            ->with('tenant')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
