<?php

namespace GeekCell\UserPolicyBundle\Trait;

use GeekCell\UserPolicyBundle\Tests\Entity\User;
use PHPUnit\Framework\TestCase;

class HasPoliciesTest extends TestCase
{
    public function testCan()
    {
        $subject = new User();

        $this->assertTrue($subject->can('access'));
    }

    public function testCanNot()
    {
        $subject = new User();

        $this->assertFalse($subject->cannot('access'));
    }
}
