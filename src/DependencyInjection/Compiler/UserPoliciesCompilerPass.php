<?php

declare(strict_types=1);

namespace GeekCell\UserPolicyBundle\DependencyInjection\Compiler;

use GeekCell\UserPolicyBundle\Support\Attribute\AsPolicy;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers all policies with tag 'geek_cell.user_policy.policy' in registry
 *
 * @package GeekCell\UserPolicyBundle\DependencyInjection\Compiler
 */
class UserPoliciesCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $policyRegistryService = $container->getDefinition('geek_cell.user_policy.policy_registry');

        // Get all tagged services with tag name 'geek_cell.user_policy.policy'
        $taggedServices = $container->findTaggedServiceIds('geek_cell.user_policy.policy');
        foreach ($taggedServices as $id => $tags) {
            // Get target subject class from attribute
            $reflClass = $container->getReflectionClass($id);
            foreach ($reflClass->getAttributes() as $attribute) {
                if ($attribute->getName() === AsPolicy::class) {
                    $entityClass = $attribute->newInstance()->getSubjectClass();

                    // Add method call to register policy in registry
                    $policyRegistryService->addMethodCall('register', [$entityClass, new Reference($id)]);
                    break;
                }
            }
        }
    }
}
