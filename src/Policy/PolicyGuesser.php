<?php

declare(strict_types=1);

namespace GeekCell\UserPolicyBundle\Policy;

/**
 * Guesses the policy class for the given class.
 *
 * @package GeekCell\UserPolicyBundle\Policy
 */
class PolicyGuesser
{
    private const POSSIBLE_NAMESPACES = [
        'App\\Policy\\',
        'App\\Policies\\',
        'App\\Auth\\Policy\\',
        'App\\Auth\\Policies\\',
        'App\\Entity\\Policy\\',
        'App\\Entity\\Policies\\',
        'App\\Security\\Policy\\',
        'App\\Security\\Policies\\',
    ];

    /**
     * Guesses the policy class for the given class or returns null if it can't be guessed.
     *
     * @param class-string $class
     * @return class-string<Policy>|null
     */
    public function guess(string $class): ?string
    {
        // Get the class name without namespace
        $className = substr($class, strrpos($class, '\\') + 1);

        // Try to find the policy class in the possible namespaces
        foreach (self::POSSIBLE_NAMESPACES as $namespace) {
            $policyClass = $namespace . $className . 'Policy';
            if (class_exists($policyClass) && is_subclass_of($policyClass, Policy::class)) {
                return $policyClass;
            }
        }

        // Otherwise, look below the class' namespace
        $namespace = substr($class, 0, strrpos($class, '\\'));
        $possibleNamespaces = [
            $namespace,
            $namespace . '\\Policy\\',
            $namespace . '\\Policies\\',
        ];

        foreach ($possibleNamespaces as $namespace) {
            $policyClass = $namespace . '\\' . $className . 'Policy';
            if (class_exists($policyClass) && is_subclass_of($policyClass, Policy::class)) {
                return $policyClass;
            }
        }

        return null;
    }
}
