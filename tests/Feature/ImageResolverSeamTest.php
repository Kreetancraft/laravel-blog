<?php

use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Blog\Tests\Fixtures\StubImageResolver;

/**
 * Images come from a configured resolver, not from a trait.
 *
 * These run with no media package installed, which is the point: the contract
 * is a shape, not an interface, so it has to be asserted by satisfying it.
 */
beforeEach(function (): void {
    StubImageResolver::reset();
    config()->set('blog.image_resolver', StubImageResolver::class);
});

it('resolves a post featured image through the configured resolver', function (): void {
    $post = Post::factory()->published()->create();
    StubImageResolver::give($post, 'featured', '/img/featured.jpg');

    expect($post->featuredUrl())->toBe('/img/featured.jpg')
        ->and(Post::imagesEnabled())->toBeTrue();
});

it('resolves an author avatar and a series cover from their own collections', function (): void {
    $author = Author::factory()->create();
    $series = Series::factory()->create();

    StubImageResolver::give($author, 'avatar', '/img/avatar.jpg');
    StubImageResolver::give($series, 'cover', '/img/cover.jpg');

    expect($author->avatarUrl())->toBe('/img/avatar.jpg')
        ->and($series->coverUrl())->toBe('/img/cover.jpg');
});

it('sends the featured image to the public API', function (): void {
    $post = Post::factory()->published()->create();
    StubImageResolver::give($post, 'featured', '/img/featured.jpg');

    $this->getJson(route('api.v1.posts.show', $post->slug))
        ->assertOk()
        ->assertJsonPath('data.featured_image', '/img/featured.jpg');
});

it('preloads a page of posts rather than resolving one at a time', function (): void {
    // Without this the listing is an N+1 by construction, which is the cost of
    // resolving images without a relation to eager-load.
    Post::factory()->published()->count(3)->create();

    $this->getJson(route('api.v1.posts.index'))->assertOk();

    expect(StubImageResolver::$preloaded)->toHaveCount(3);
});

it('honours a collection renamed in config', function (): void {
    config()->set('blog.collections.featured', 'hero');

    $post = Post::factory()->published()->create();
    StubImageResolver::give($post, 'hero', '/img/hero.jpg');

    expect($post->featuredUrl())->toBe('/img/hero.jpg');
});

it('saves picked images back through the resolver', function (): void {
    $post = Post::factory()->published()->create();

    $post->syncAttachedMedia([7], 'featured');

    expect($post->featuredUrl())->toBe('/stub/7.jpg');
});

it('is inert when the configured class does not exist', function (): void {
    // A stale config value after a package is removed must not take the blog
    // down; it should behave exactly as if nothing were configured.
    config()->set('blog.image_resolver', 'Acme\\Nope\\MissingResolver');

    $post = Post::factory()->published()->create();

    expect($post->featuredUrl())->toBeNull()
        ->and(Post::imagesEnabled())->toBeFalse();
});
