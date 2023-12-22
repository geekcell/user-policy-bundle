<?php

declare(strict_types=1);

namespace GeekCell\UserPolicyBundle\Test\Trait;

use GeekCell\Facade\Facade;
use GeekCell\UserPolicyBundle\Contracts\Policy;
use GeekCell\UserPolicyBundle\Policy\PolicyGuesser;
use GeekCell\UserPolicyBundle\Policy\PolicyRegistry;
use GeekCell\UserPolicyBundle\Test\Fixture\Container;
use GeekCell\UserPolicyBundle\Test\Fixture\User;
use GeekCell\UserPolicyBundle\Trait\HasPolicies;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class Dummy {}

class DummyPolicy implements Policy {
    public function doSomething(): bool
    {
        return true;
    }

    public function doNothing(): void
    {
    }
}

final class HasPoliciesTest extends TestCase
{
    private User $user;

    private PolicyRegistry $policyRegistry;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->policyRegistry = new PolicyRegistry(new PolicyGuesser());
        $facadeContainer = new Container($this->policyRegistry);

        Facade::setContainer($facadeContainer);
    }

    protected function tearDown(): void
    {
        Facade::clear();
    }

    public function testCanReturnsFalseIfSubjectIsNotAValidClass(): void
    {
        $this->assertFalse($this->user->can('doSomething', 'invalid-class'));
    }

    public function testCanThrowsExceptionIfNoPolicyRegisteredForSubject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Could not find policy");

        $this->user->can('doSomething', new Dummy());
    }

    public function testCanReturnsFalseIfAbilityDoesNotExistOnPolicy(): void
    {
        $this->policyRegistry->register(Dummy::class, new DummyPolicy());
        $this->assertFalse($this->user->can('somethingElse', new Dummy()));
    }

    public function testCanThrowsExceptionIfPolicyAbilityDoesNotReturnBool(): void
    {
        $this->policyRegistry->register(Dummy::class, new DummyPolicy());
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('was expected to return a boolean value');

        $this->user->can('doNothing', new Dummy());
    }

    public function testCanReturnsPolicyResult(): void
    {
        $policy = new DummyPolicy();
        $this->policyRegistry->register(Dummy::class, $policy);
        $this->assertSame($policy->doSomething(), $this->user->can('doSomething', new Dummy()));
    }

    public function testCanPassesCorrectArguments(): void
    {
        $additionalParameter = new Dummy();
        $subject = new Dummy();
        $policy = new class($this, [$this->user, $subject, $additionalParameter]) implements Policy {
            public function __construct(
                private readonly TestCase $testCase,
                /** @var list<mixed> */
                private array $expectedArguments,
            ) {
            }

            public function doSomething(...$args): bool
            {
                $this->testCase->assertSame($this->expectedArguments, $args);

                return true;
            }
        };

        $this->policyRegistry->register(Dummy::class, $policy);
        $this->user->can('doSomething', $subject, $additionalParameter);
    }

    public function testIsThrowsExceptionIfObjectIsNotUserInterface(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $notUser = new class() {
            use HasPolicies;
        };

        $notUser->is('ROLE_SOMETHING');
    }

    public function testIsIgnoresRolePrefix(): void
    {
        $this->user->addRole('ROLE_SOMETHING');

        $this->assertTrue($this->user->is('something'));
    }

    public function testWorksAsExpected(): void
    {
        $this->user->addRole('SOME_ROLE');
        $this->user->addRole('ROLE_SOME_OTHER_ROLE');

        $this->assertTrue($this->user->is('some_role'));
        $this->assertTrue($this->user->is('some_other_role'));
        $this->assertFalse($this->user->is('not_this_role'));
        $this->assertFalse($this->user->is('or_this_role'));
    }
}
