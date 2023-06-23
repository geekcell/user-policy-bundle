<?php

declare(strict_types=1);

namespace GeekCell\UserPolicyBundle\Policy;

use GeekCell\UserPolicyBundle\Contracts\Policy;

/**
 * Registry for policies.
 *
 * @package GeekCell\UserPolicyBundle\Policy
 */
class PolicyRegistry
{
    /**
     * @var array <class-string, Policy>
     */
    private array $registry = [];

    /**
     * PolicyRegistry constructor.
     *
     * @param PolicyGuesser $guesser
     */
    public function __construct(
        private readonly PolicyGuesser $guesser,
    ) {
    }

    /**
     * Register policy in registry
     *
     * @param class-string $class
     * @param Policy $policy
     */
    public function register(string $class, Policy $policy): void
    {
        $this->registry[$class] = $policy;
    }

    /**
     * Get policy from registry. If policy not found, try to guess it.
     *
     * @param class-string $class
     * @return Policy|null
     */
    public function get(string $class): ?Policy
    {
        if (isset($this->registry[$class])) {
            return $this->registry[$class];
        }

        $guessedPolicyClass = $this->guesser->guess($class);
        if ($guessedPolicyClass === null) {
            return null;
        }

        $this->registry[$class] = (new \ReflectionClass($guessedPolicyClass))->newInstanceWithoutConstructor();

        return $this->registry[$class];
    }
}
