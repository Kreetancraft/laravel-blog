<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Comment;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Blog\Models\Tag;
use Kreetancraft\Blog\Policies\PostPolicy;
use Kreetancraft\Blog\Policies\SeriesPolicy;
use Kreetancraft\Seo\Support\SeoModelRegistry;
use Kreetancraft\Seo\Support\SitemapProviderRegistry;

/**
 * What this package contributes to its neighbours, and what it refuses to
 * depend on.
 *
 * Each of these is a seam that has no compiler to protect it: nothing fails to
 * load when a container tag stops being collected, so the wiring is only real
 * if a test says so.
 */
it('contributes its SEO-enabled models to the seo package', function (): void {
    // Through the container tag alone — config('seo.models') is untouched.
    expect(config('seo.models'))->toBe([]);

    $registered = array_keys(app(SeoModelRegistry::class)->all());

    expect($registered)
        ->toContain(Post::class)
        ->toContain(Category::class)
        ->toContain(Author::class)
        ->toContain(Series::class);
});

it('contributes its sitemap urls to the seo package', function (): void {
    expect(config('seo.sitemap_providers'))->toBe([]);

    expect(app(SitemapProviderRegistry::class)->all())->not->toBeEmpty();
});

it('appears in the sitemap feed for a published post', function (): void {
    $post = Post::factory()->published()->create(['slug' => 'a-published-post']);

    $paths = collect($this->getJson(route('api.seo.sitemap'))->assertOk()->json('urls'))
        ->pluck('path');

    expect($paths)->toContain('/blog/a-published-post');
});

it('contributes sidebar links without naming the package that renders them', function (): void {
    $items = [];

    foreach (app()->tagged('admin.navigation') as $contribution) {
        $items = array_merge($items, array_is_list($contribution) ? $contribution : [$contribution]);
    }

    expect(collect($items)->pluck('label'))
        ->toContain('Posts')
        ->toContain('Comments');
});

it('gates every sidebar link on the same policy question its route asks', function (): void {
    // A link the policy denies but the route allows (or the reverse) sends
    // people to a 403 from their own sidebar.
    $items = [];

    foreach (app()->tagged('admin.navigation') as $contribution) {
        $items = array_merge($items, array_is_list($contribution) ? $contribution : [$contribution]);
    }

    foreach ($items as $item) {
        $route = Route::getRoutes()->getByName($item['route']);

        expect($route)->not->toBeNull("route {$item['route']} is not registered")
            ->and($route->gatherMiddleware())
            ->toContain('can:'.$item['ability'].','.$item['model']);
    }
});

it('declares a subject on every policy, so permissions can be discovered', function (): void {
    // kreetancraft/laravel-user-management discovers policies through
    // Gate::policies(), but only generates permissions for those that opt in.
    // A policy without this is enforced and ungrantable.
    $policies = [
        Post::class, Comment::class, Tag::class,
        Category::class, Author::class, Series::class,
    ];

    foreach ($policies as $model) {
        $policy = Gate::getPolicyFor($model);

        expect($policy)->not->toBeNull()
            ->and(defined($policy::class.'::PERMISSION_SUBJECT'))->toBeTrue();
    }
});

it('names abilities outside CRUD so they are generated too', function (): void {
    // publish() and moderate() are checked by the policies. An ability nobody
    // can create fails for everyone, which is the drift this guards.
    expect(PostPolicy::PERMISSION_EXTRA_METHODS)->toContain('publish');
});

it('leaves pluralisation to the inflector where it is already right', function (): void {
    // `blog-series` pluralises to itself, so declaring PERMISSION_SUBJECT_PLURAL
    // here would be a redundant override — the kind that rots. The constant
    // exists for subjects the inflector gets wrong, like `seo` -> `seos`.
    expect(Str::plural(SeriesPolicy::PERMISSION_SUBJECT))->toBe('blog-series')
        ->and(defined(SeriesPolicy::class.'::PERMISSION_SUBJECT_PLURAL'))->toBeFalse();
});

it('works with no image resolver installed', function (): void {
    config()->set('blog.image_resolver', null);

    $post = Post::factory()->published()->create();

    expect($post->featuredUrl())->toBeNull()
        ->and(Post::imagesEnabled())->toBeFalse();

    // And the API still serves it, with a null image rather than an error.
    $this->getJson(route('api.v1.posts.show', $post->slug))
        ->assertOk()
        ->assertJsonPath('data.featured_image', null);
});

it('groups all six subjects under one sidebar heading', function (): void {
    // Six loose links would take six lines of someone's sidebar for one
    // package. The group takes one.
    //
    // Read this package's own binding rather than the whole tag: the SEO
    // package contributes to the same tag, and asserting over both would make
    // this test fail whenever a neighbour adds a screen.
    $items = app('blog.navigation.items');

    expect(collect($items)->pluck('label')->all())
        ->toEqualCanonicalizing(['Posts', 'Categories', 'Tags', 'Authors', 'Series', 'Comments'])
        ->and(collect($items)->pluck('group')->unique()->all())->toBe(['Blogs']);
});

it('gates every one of the six on its own policy', function (): void {
    // A shared heading must not mean a shared permission: someone who may
    // moderate comments but not edit posts should see one entry, not six.
    $items = app('blog.navigation.items');

    $models = collect($items)->pluck('model')->unique();

    expect($models)->toHaveCount(6)
        ->and(collect($items)->pluck('ability')->unique()->all())->toBe(['viewAny']);
});
