<?php

declare(strict_types=1);

namespace GeekCell\UserPolicyBundle\Test\Fixture;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array<class-string, object>
     */
    private array $services;

    /**
     * @param object ...$services
     */
    public function __construct(...$services)
    {
        foreach ($services as $service) {
            $this->services[$service::class] = $service;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            return null;
        }

        return $this->services[$id];
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
}
