<?php

namespace GeekCell\UserPolicyBundle\Tests\Entity;

use GeekCell\UserPolicyBundle\Trait\HasPolicies;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    use HasPolicies;

    public function getId(): int
    {
        return 1;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->getId();
    }
}
