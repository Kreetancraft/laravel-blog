<?php

use Kreetancraft\Blog\Enums\PostStatus;
use Kreetancraft\Blog\Livewire\CreatePost;
use Kreetancraft\Blog\Livewire\EditPost;
use Kreetancraft\Blog\Livewire\ManagePosts;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Tag;
use Livewire\Livewire;

test('guests are redirected from the posts list', function () {
    $this->get(route('admin.blog.posts'))->assertRedirect(route('login'));
});

test('a role without view-blogs cannot reach the posts list', function () {
    actingAsOutsider();

    $this->get(route('admin.blog.posts'))->assertForbidden();
});

test('a package manager can view the posts list', function () {
    actingAsBlogManager();
    Post::factory()->create(['title' => 'Listed Post']);

    Livewire::test(ManagePosts::class)
        ->assertStatus(200)
        ->assertSee('Listed Post');
});

test('the posts list filters by status', function () {
    actingAsBlogManager();
    Post::factory()->published()->create(['title' => 'Live Post']);
    Post::factory()->draft()->create(['title' => 'Draft Post']);

    Livewire::test(ManagePosts::class)
        ->set('statusFilter', 'published')
        ->assertSee('Live Post')
        ->assertDontSee('Draft Post');
});

test('the posts list filters by category', function () {
    actingAsBlogManager();
    $category = Category::factory()->create();
    $inCategory = Post::factory()->create(['title' => 'Categorised Post']);
    $inCategory->categories()->sync([$category->id]);
    Post::factory()->create(['title' => 'Uncategorised Post']);

    Livewire::test(ManagePosts::class)
        ->set('categoryFilter', (string) $category->id)
        ->assertSee('Categorised Post')
        ->assertDontSee('Uncategorised Post');
});

test('the posts list searches by title', function () {
    actingAsBlogManager();
    Post::factory()->create(['title' => 'Everest Basics']);
    Post::factory()->create(['title' => 'Annapurna Notes']);

    Livewire::test(ManagePosts::class)
        ->set('search', 'Everest')
        ->assertSee('Everest Basics')
        ->assertDontSee('Annapurna Notes');
});

test('a package manager can create a post with categories and tags', function () {
    actingAsBlogManager();
    $author = Author::factory()->create();
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();

    Livewire::test(CreatePost::class)
        ->set('title', 'My New Post')
        ->set('excerpt', 'A short summary.')
        ->set('content', '<p>Body text.</p>')
        ->set('status', 'draft')
        ->set('author_id', $author->id)
        ->set('categories', [$category->id])
        ->set('tags', [$tag->id])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $post = Post::firstWhere('title', 'My New Post');

    expect($post)->not->toBeNull()
        ->and($post->slug)->toBe('my-new-post')
        ->and($post->status)->toBe(PostStatus::Draft)
        ->and($post->categories()->pluck('blog_categories.id')->all())->toBe([$category->id])
        ->and($post->tags()->pluck('blog_tags.id')->all())->toBe([$tag->id]);
});

test('duplicate titles get suffixed slugs', function () {
    actingAsBlogManager();
    $author = Author::factory()->create();
    Post::factory()->create(['title' => 'Same Title']);

    Livewire::test(CreatePost::class)
        ->set('title', 'Same Title')
        ->set('status', 'draft')
        ->set('author_id', $author->id)
        ->call('save')
        ->assertHasNoErrors();

    expect(Post::where('slug', 'like', 'same-title%')->count())->toBe(2)
        ->and(Post::where('slug', 'same-title-1')->exists())->toBeTrue();
});

test('publishing without a date stamps published_at with now', function () {
    actingAsBlogManager();
    $author = Author::factory()->create();

    Livewire::test(CreatePost::class)
        ->set('title', 'Instant Publish')
        ->set('status', 'published')
        ->set('author_id', $author->id)
        ->call('save')
        ->assertHasNoErrors();

    $post = Post::firstWhere('title', 'Instant Publish');

    expect($post->status)->toBe(PostStatus::Published)
        ->and($post->published_at)->not->toBeNull();
});

test('a scheduled post requires a future publish date', function () {
    actingAsBlogManager();
    $author = Author::factory()->create();

    Livewire::test(CreatePost::class)
        ->set('title', 'Scheduled Post')
        ->set('status', 'scheduled')
        ->set('published_at', now()->subDay()->format('Y-m-d\TH:i'))
        ->set('author_id', $author->id)
        ->call('save')
        ->assertHasErrors(['published_at']);
});

test('a package manager can edit a post', function () {
    actingAsBlogManager();
    $post = Post::factory()->create(['title' => 'Old Title']);

    Livewire::test(EditPost::class, ['post' => $post])
        ->set('title', 'New Title')
        ->call('save')
        ->assertHasNoErrors();

    expect($post->fresh()->title)->toBe('New Title');
});

test('editing keeps the slug unique to itself', function () {
    actingAsBlogManager();
    $post = Post::factory()->create(['title' => 'Keep Me']);

    Livewire::test(EditPost::class, ['post' => $post])
        ->set('excerpt', 'Updated excerpt.')
        ->call('save')
        ->assertHasNoErrors();

    expect($post->fresh()->excerpt)->toBe('Updated excerpt.');
});

test('deleting a post soft-deletes it', function () {
    actingAsBlogManager();
    $post = Post::factory()->create();

    Livewire::test(ManagePosts::class)
        ->call('confirmDelete', $post->id)
        ->call('delete');

    expect(Post::find($post->id))->toBeNull()
        ->and(Post::withTrashed()->find($post->id))->not->toBeNull();
});
