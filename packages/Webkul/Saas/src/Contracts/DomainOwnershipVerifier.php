<?php

namespace Webkul\Saas\Contracts;

use Webkul\Saas\Contracts\DomainChallenge as DomainChallengeContract;

interface DomainOwnershipVerifier
{
    /**
     * Verify a challenge placed by the domain owner.
     */
    public function verify(DomainChallengeContract $challenge, string $hostname): bool;
}
