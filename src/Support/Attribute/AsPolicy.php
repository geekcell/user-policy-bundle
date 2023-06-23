<?php

declare(strict_types=1);

namespace GeekCell\UserPolicyBundle\Support\Attribute;

use Attribute;

/**
 * Attribute for policy class to specify subject class
 *
 * @package GeekCell\UserPolicyBundle\Support\Attribute
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AsPolicy
{
    /**
     * AsPolicy constructor.
     *
     * @param string $subjectClass
     */
    public function __construct(
        private readonly string $subjectClass,
    ) {
    }

    /**
     * Get subject class
     *
     * @return string
     */
    public function getSubjectClass(): string
    {
        return $this->subjectClass;
    }
}
