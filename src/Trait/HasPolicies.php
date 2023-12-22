<?php

declare(strict_types=1);

namespace GeekCell\UserPolicyBundle\Trait;

use BadMethodCallException;
use GeekCell\UserPolicyBundle\Contracts\Policy;
use GeekCell\UserPolicyBundle\Policy\PolicyRegistry;
use GeekCell\UserPolicyBundle\Support\Facade\PolicyRegistry as PolicyRegistryFacade;
use InvalidArgumentException;
use ReflectionException;
use Symfony\Component\Security\Core\User\UserInterface;
use UnexpectedValueException;

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
     * @param mixed ...$extraArgs
     *
     * @return bool
     *
     * @throws UnexpectedValueException
     * @throws ReflectionException
     */
    public function can(string $ability, string|object $subject, mixed ...$extraArgs): bool
    {
        $methodArgs = [];
        if (is_object($subject)) {
            $methodArgs[] = $subject;
            $subject = $subject::class;
        }

        if (!class_exists($subject)) {
            return false;
        }

        /** @see PolicyRegistry::get() */
        $policy = PolicyRegistryFacade::get($subject);
        if ($policy === null) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not find policy for subject "%s". Try implementing the %s (marker) interface (and configuring it in your services.yaml file) or manually registering the policy for the class by calling %s::register',
                    $subject,
                    PolicyRegistry::class,
                    Policy::class
                )
            );
        }

        if (!method_exists($policy, $ability)) {
            return false;
        }

        $result = $policy->{$ability}($this, ...$methodArgs, ...$extraArgs);
        if (!is_bool($result)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Method "%s" of policy "%s" was expected to return a boolean value. Instead it returned a value of type "%s".',
                    $ability,
                    $policy::class,
                    get_debug_type($result)
                )
            );
        }

        return $result;
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
     * Check if user has role.
     * User must implement UserInterface.
     *
     * @param string $role
     * @return bool
     * @throws BadMethodCallException
     */
    public function is(string $role): bool
    {
        if (!$this instanceof UserInterface) {
            throw new BadMethodCallException(
                sprintf(
                    'Class "%s" must implement "%s" interface.',
                    static::class,
                    UserInterface::class
                )
            );
        }

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
     * @throws BadMethodCallException
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

        throw new BadMethodCallException(sprintf('Method "%s" does not exist.', $name));
    }
}
