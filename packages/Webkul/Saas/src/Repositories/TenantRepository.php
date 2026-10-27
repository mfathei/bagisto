<?php

namespace Webkul\Saas\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\TenantStatus;

class TenantRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Tenant::class;
    }

    /**
     * Find a tenant by slug.
     */
    public function findBySlug(string $slug): ?Tenant
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Find a tenant by UUID.
     */
    public function findByUuid(string $uuid): ?Tenant
    {
        return $this->model->where('uuid', $uuid)->first();
    }

    /**
     * Filtered, sorted and paginated tenant listing for the Super Admin panel.
     */
    public function paginateFiltered(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['plan', 'currentSubscription', 'domains'])
            ->when(
                ! empty($filters['search']),
                fn ($query) => $query->where(function ($query) use ($filters) {
                    $term = '%'.$filters['search'].'%';

                    $query->where('name', 'like', $term)
                        ->orWhere('slug', 'like', $term)
                        ->orWhere('email', 'like', $term);
                })
            )
            ->when(
                ! empty($filters['status']),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['plan_id']),
                fn ($query) => $query->where('plan_id', $filters['plan_id'])
            )
            ->when(
                ! empty($filters['from']),
                fn ($query) => $query->whereDate('created_at', '>=', $filters['from'])
            )
            ->when(
                ! empty($filters['to']),
                fn ($query) => $query->whereDate('created_at', '<=', $filters['to'])
            )
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Count tenants grouped by status.
     *
     * @return array<string, int>
     */
    public function countsByStatus(): array
    {
        $counts = $this->model->newQuery()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $result = [];

        foreach (TenantStatus::cases() as $status) {
            $result[$status->value] = (int) ($counts[$status->value] ?? 0);
        }

        return $result;
    }

    /**
     * Tenants whose trial ends before the given date and still trialing.
     */
    public function trialsEndingBefore($date)
    {
        return $this->model->newQuery()
            ->where('status', TenantStatus::Active->value)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', $date)
            ->get();
    }

    /**
     * Count registrations created since a point in time.
     */
    public function createdSince($date): int
    {
        return $this->model->newQuery()
            ->where('created_at', '>=', $date)
            ->count();
    }

    /**
     * The most recently registered tenants, with their domains.
     */
    public function latest(int $limit = 5)
    {
        return $this->model->newQuery()
            ->with(['domains'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Count registrations created inside a window.
     */
    public function createdBetween($from, $to): int
    {
        return $this->model->newQuery()
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    /**
     * Registration timestamps inside a window, for charting.
     */
    public function createdAtBetween($from, $to)
    {
        return $this->model->newQuery()
            ->whereBetween('created_at', [$from, $to])
            ->pluck('created_at');
    }
}
