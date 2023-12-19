<?php

declare(strict_types=1);

namespace GeekCell\UserPolicyBundle\Trait;

use GeekCell\UserPolicyBundle\Support\Facade\PolicyRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

use function Functional\map;

/**
 * HasPolicies trait
 *
 * @package GeekCell\UserPolicyBundle\Trait
 */
trait HasPolicies
{
    /**
     * Check if user can do something with subject via a policy. A subject can be a class name or an object.
     * There can be optional extra arguments, which will be passed to policy method.
     *
     * @param string $ability
     * @param class-string|object $subject
     * @param array<mixed> $extraArgs
     *
     * @return bool
     *
     * @throws \UnexpectedValueException
     */
    public function can(string $ability, mixed $subject, mixed ...$extraArgs): bool
    {
        $this->ensureUserInterface();

        $methodArgs = [$this];
        if (is_object($subject)) {
            $methodArgs[] = $subject;
            $subject = $subject::class;
        }

        if (!class_exists($subject)) {
            return false;
        }

        $policy = PolicyRegistry::get($subject);
        $policyReflectionClass = new \ReflectionClass($policy);

        if (!$policyReflectionClass->hasMethod($ability)) {
            return false;
        }

        $policyMethod = $policyReflectionClass->getMethod($ability);
        $returnType = $policyMethod->getReturnType();

        if ($returnType === null || $returnType->getName() !== 'bool') {
            throw new \UnexpectedValueException(
                sprintf('Method "%s" of policy "%s" must return bool.', $ability, $policyReflectionClass->getName())
            );
        }

        return $policyMethod->invokeArgs($policy, \array_merge($methodArgs, $extraArgs));
    }

    /**
     * Inverse of can()
     *
     * @see HasPolicies::can()
     */
    public function cannot(string $ability, mixed $subject = null, mixed ...$extraArgs): bool
    {
        return !$this->can($ability, $subject, ...$extraArgs);
    }

    /**
     * Check if user has role
     *
     * @param string $role
     * @return bool
     */
    public function is(string $role): bool
    {
        $this->ensureUserInterface();

        $normalizedRoles = map(
            $this->getRoles(),
            fn (string $symfonyRole) => mb_strtolower(str_replace('ROLE_', '', $symfonyRole)),
        );

        return in_array(mb_strtolower($role), $normalizedRoles, true);
    }

    /**
     * Inverse of is()
     *
     * @see HasPolicies::is()
     */
    public function isNot(string $role): bool
    {
        return !$this->is($role);
    }

    /**
     * Magic method to call can(), cannot(), is() and isNot() methods.
     *
     * @param string $name
     * @param array<mixed> $arguments
     *
     * @return bool
     *
     * @throws \BadMethodCallException
     */
    public function __call(string $name, array $arguments): bool
    {
        if (str_starts_with($name, 'cannot')) {
            $ability = lcfirst(substr($name, 6));
            return $this->cannot($ability, ...$arguments);
        }

        if (str_starts_with($name, 'can')) {
            $ability = lcfirst(substr($name, 3));
            return $this->can($ability, ...$arguments);
        }

        if (str_starts_with($name, 'isNot')) {
            $role = lcfirst(substr($name, 5));
            return $this->isNot($role);
        }

        if (str_starts_with($name, 'is')) {
            $role = lcfirst(substr($name, 2));
            return $this->is($role);
        }

        throw new \BadMethodCallException(sprintf('Method "%s" does not exist.', $name));
    }

    /**
     * Ensure that this trait is only used in classes, which implement UserInterface.
     *
     * @throws \BadMethodCallException
     */
    private function ensureUserInterface(): void
    {
        if (!$this instanceof UserInterface) {
            throw new \BadMethodCallException(
                sprintf(
                    'Class "%s" must implement "%s" interface.',
                    static::class,
                    UserInterface::class
                )
            );
        }
    }
}
