<?php

declare(strict_types=1);

namespace GeekCell\UserPolicyBundle\Support\Facade;

use GeekCell\Facade\Facade;
use GeekCell\UserPolicyBundle\Policy\PolicyRegistry as PolicyRegistryService;

/**
 * Facade for PolicyRegistry
 *
 * @package GeekCell\UserPolicyBundle\Support\Facade
 */
class PolicyRegistry extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return PolicyRegistryService::class;
    }
}
