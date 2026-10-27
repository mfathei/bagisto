<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\DomainChallenge;

class DomainChallengeRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return DomainChallenge::class;
    }

    /**
     * The current pending challenge of a domain.
     */
    public function pendingForDomain(int $domainId): ?DomainChallenge
    {
        return $this->model
            ->where('tenant_domain_id', $domainId)
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->first();
    }
}
