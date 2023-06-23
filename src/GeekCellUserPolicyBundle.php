<?php

declare(strict_types=1);

namespace GeekCell\UserPolicyBundle;

use GeekCell\Facade\Facade;
use GeekCell\UserPolicyBundle\DependencyInjection\Compiler\UserPoliciesCompilerPass;
use GeekCell\UserPolicyBundle\DependencyInjection\GeekCellUserPolicyExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Class GeekCellUserPolicyBundle
 *
 * @package GeekCell\UserPolicyBundle
 */
class GeekCellUserPolicyBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new UserPoliciesCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new GeekCellUserPolicyExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        parent::boot();

        Facade::setContainer($this->container);
    }
}
