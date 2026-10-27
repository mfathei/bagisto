<?php

namespace Webkul\Saas\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\ContactInquiry;
use Webkul\Saas\Enums\ContactInquiryStatus;

class ContactInquiryRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return ContactInquiry::class;
    }

    /**
     * Filtered listing for the Super Admin panel.
     */
    public function paginateFiltered(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with('handler')
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['type']), fn ($query) => $query->where('inquiry_type', $filters['type']))
            ->when(
                ! empty($filters['search']),
                fn ($query) => $query->where(function ($query) use ($filters) {
                    $term = '%'.$filters['search'].'%';

                    $query->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('subject', 'like', $term);
                })
            )
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find an inquiry by its public UUID.
     */
    public function findByUuid(string $uuid): ?ContactInquiry
    {
        return $this->model->newQuery()->where('uuid', $uuid)->first();
    }

    /**
     * Count inquiries that still need attention.
     */
    public function openCount(): int
    {
        return $this->model->newQuery()
            ->whereIn('status', [
                ContactInquiryStatus::New->value,
                ContactInquiryStatus::InProgress->value,
            ])
            ->count();
    }
}
