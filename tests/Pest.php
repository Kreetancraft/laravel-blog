<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Kreetancraft\Blog\Tests\Fixtures\Models\User;
use Kreetancraft\Blog\Tests\TestCase;
use Spatie\Permission\Models\Permission;

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature', 'Unit');

/**
 * Every permission this package ships behaviour for.
 *
 * The package seeds none at runtime — they are generated from its policies by
 * kreetancraft/laravel-user-management. Tests declare them explicitly so the
 * suite is self-contained and carries no host vocabulary.
 *
 * @return list<string>
 */
function blogPermissions(): array
{
    $names = [];

    foreach (['post', 'blog-category', 'blog-tag', 'blog-author', 'blog-series', 'blog-comment'] as $subject) {
        $plural = $subject === 'blog-series' ? $subject : Str::plural($subject);

        foreach (['view', 'create', 'update', 'delete'] as $action) {
            $names[] = $action.'-'.$plural;
        }
    }

    $names[] = 'publish-posts';
    $names[] = 'moderate-blog-comments';

    return $names;
}

/**
 * Create every blog permission, without granting any.
 *
 * Creating them is what switches enforcement on: the policies treat an
 * application with no permissions at all as open, so a test asserting a denial
 * has to establish that permissions are in use first.
 */
function seedBlogPermissions(): void
{
    foreach (blogPermissions() as $name) {
        Permission::findOrCreate($name, 'web');
    }
}

function makeBlogUser(string ...$permissions): User
{
    seedBlogPermissions();

    $user = User::create([
        'name' => 'Tester',
        'email' => 'tester'.uniqid().'@example.com',
        'password' => bcrypt('secret-password'),
    ]);

    foreach ($permissions as $permission) {
        $user->givePermissionTo($permission);
    }

    return $user;
}

/**
 * Someone who runs the blog: every blog permission.
 *
 * Replaces the host application's `actingAsRole(UserRole::PackageManager)` —
 * that enum was deleted when user management was extracted, and roles are no
 * longer seeded by any package. What the tests actually needed was "a user who
 * may do blog things", which is this.
 */
function actingAsBlogManager(): User
{
    $user = makeBlogUser(...blogPermissions());
    test()->actingAs($user);

    return $user;
}

/**
 * Someone with no blog permissions, on an application that uses permissions.
 *
 * Replaces `actingAsRole(UserRole::FinanceAdmin)`, which in the host was simply
 * a role that held none of the blog permissions.
 */
function actingAsOutsider(): User
{
    $user = makeBlogUser();
    test()->actingAs($user);

    return $user;
}
