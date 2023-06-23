<?php

declare(strict_types=1);

namespace GeekCell\UserPolicyBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class GeekCellUserPolicyExtension
 *
 * @package GeekCell\UserPolicyBundle\DependencyInjection
 */
class GeekCellUserPolicyExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $locator = new FileLocator(__DIR__ . '/../../config');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yaml');
    }
}
