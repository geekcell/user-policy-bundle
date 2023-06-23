# Symfony Bundle for User Policies

This Symfony bundle provides an opinionated way to implement user policies for your app. User policies are useful when you need fine-grained/complex rules regarding access to a particular resource. These policies are not static configurations, but written in PHP, i.e. you get full flexibility, but without cluttering your code base.

## Example

Let's say you are working on a video platform where users are only allowed to upload if they either have a premium subscription or a remaining upload quota.

```php
#[AsPolicy(Video::class)]
class VideoPolicy implements Policy
{
    public function __construct(
        private readonly QuotaService $quotaService,
    ) {
    }

    public function upload(User $user): bool
    {
        return (
            $user->hasPremiumSubscription() || 
            $this->quotaService->getRemainingUserUploads($user) > 0
        );
    }
}
```

```php
class VideoController extends AbstractController
{
    #[Route('/videos/new_upload')]
    public function create(): Response
    {
        if ($this->getUser()->canUpload(Video::class)) {
            // Proceed with upload ...
        }

        $this->createAccessDeniedException('Operation not allowed.');
    }
}
```

Pretty nice, isn't it? The business logic is encapsulated in policy classes and can be _magically queried_ directly from the user object.

## Installation

To use this bundle, require it in Composer

```bash
composer require geekcell/user-policy-bundle
```

When installed, add the following lines in your `config/services.yaml`

```yaml
services:

    # Add these lines below to your services.yaml

    _instanceof:
        GeekCell\UserPolicyBundle\Contracts\Policy:
            tags: ['geek_cell.user_policy.policy']
```

These lines are crucial for Symfony to auto-discover the policies defined in your app. Alternatively, policies can be manually configured or even guessed by name, but these methods are not recommended.

Please also the `HasPolicies` trait to you user class.

```php
<?php

namespace App\Security;

use GeekCell\UserPolicyBundle\Trait\HasPolicies;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use HasPolicies;

    // ...
}
```

You are now ready to go.

## Writing Policies

A basic policy looks like this:

```php
<?php

namespace App\Security\Policy;

use App\Entity\Book;
use App\Security\User;
use GeekCell\UserPolicyBundle\Contracts\Policy;
use GeekCell\UserPolicyBundle\Support\Attribute\AsPolicy;

#[AsPolicy(Book::class)]
class BookPolicy implements Policy
{
    public function create(User $user): bool
    {
        // ...
    }

    public function update(User $user, Book $book): bool
    {
        // ...
    }

    public function delete(User $user, Book $book, mixed $someArguments): bool
    {
        // ...
    }
}
```

Let's break it down:

- A policy must implement the `GeekCell\UserPolicyBundle\Contracts\Policy` marker interface.
- Use the `#[AsPolicy]` attribute to associate a policy to a subject.
- The policy methods can have arbitrary names, i.e. they're not limited to CRUD operations.
    - The methods always take the current `User` as their first argument.
    - The second argument can optionally be an instance of the current subject.
    - The remaining arguments are purely optional and can be used as needed.

For the time being, you can return either `true` or `false` as indication whether a user is allowed to perform some action on a subject (or subject instance).

## Checking Policies

With the `HasPolicies` trait, your `User` instance now has some new magical abilities:

```php
// These below will internally call BookPolicy::create($user)
$user->can('create', 'App\Entity\Book');
$user->canCreate('App\Entity\Book');
$user->cannot('create', 'App\Entity\Book');
$user->cannotCreate('App\Entity\Book');

// These will call BookPolicy::update($user, $book)
$user->can('update', $book);
$user->canUpdate($book);
$user->cannot('update', $book);
$user->canUpdate($book);

// And these will call BookPolicy::delete($user, $book, $foo, $bar, $baz)
$user->can('delete', $book, $foo, $bar, $baz);
$user->canDelete($book, $foo, $bar, $baz);
$user->cannot('delete', $book, $foo, $bar, $baz);
$user->cannnotDelete($book, $foo, $bar, $baz);
```

## Roles Helper

In many cases, policies are likely to have some relationship to one or more rules associated with a user. This bundle provides some convenience methods to make roles easier to query.

```php
$user->setRoles(['MANAGER_ROLE', 'EDITOR_ROLE']);

$user->is('editor'); // true
$user->isEditor(); // true
$user->is('manager'); // true
$user->isManager(); // true
$user->isNot('admin'); // true
$user->isNotAdmin(); // true
```

## Inspiration(s)

- Laravel Authorization (https://laravel.com/docs/authorization)
