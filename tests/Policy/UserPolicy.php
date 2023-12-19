<?php

namespace GeekCell\UserPolicyBundle\Tests\Policy;

use GeekCell\UserPolicyBundle\Support\Attribute\AsPolicy;
use GeekCell\UserPolicyBundle\Tests\Entity\User;

#[AsPolicy(User::class)]
class UserPolicy
{
    public function access(User $test): bool
    {
        return $test->getId() === 1;
    }
}
