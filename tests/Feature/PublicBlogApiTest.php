<?php

use Kreetancraft\Blog\Enums\CommentStatus;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Comment;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Tag;

test('the index returns only published posts', function () {
    Post::factory()->published()->create(['title' => 'Visible Post']);
    Post::factory()->draft()->create(['title' => 'Draft Post']);
    Post::factory()->scheduled()->create(['title' => 'Scheduled Post']);
    Post::factory()->archived()->create(['title' => 'Archived Post']);
    Post::factory()->published()->create(['title' => 'Deleted Post'])->delete();

    $this->getJson(route('api.v1.posts.index'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Visible Post');
});

test('the index filters by category slug', function () {
    $category = Category::factory()->create(['name' => 'Filtered']);
    $inCategory = Post::factory()->published()->create(['title' => 'In Category']);
    $inCategory->categories()->sync([$category->id]);
    Post::factory()->published()->create(['title' => 'Out of Category']);

    $this->getJson(route('api.v1.posts.index', ['filter' => ['category' => $category->slug]]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'In Category');
});

test('the index filters by tag slug', function () {
    $tag = Tag::factory()->create();
    $tagged = Post::factory()->published()->create(['title' => 'Tagged Post']);
    $tagged->tags()->sync([$tag->id]);
    Post::factory()->published()->create(['title' => 'Untagged Post']);

    $this->getJson(route('api.v1.posts.index', ['filter' => ['tag' => $tag->slug]]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Tagged Post');
});

test('the index searches title and content', function () {
    Post::factory()->published()->create(['title' => 'Everest Guide']);
    Post::factory()->published()->create(['title' => 'Other Post']);

    $this->getJson(route('api.v1.posts.index', ['filter' => ['search' => 'Everest']]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Everest Guide');
});

test('an unknown filter value is rejected', function () {
    $this->getJson(route('api.v1.posts.index', ['filter' => ['category' => 'does-not-exist']]))
        ->assertUnprocessable();
});

test('the detail endpoint returns content, seo, and approved comments only', function () {
    $post = Post::factory()->published()->create([
        'title' => 'Detail Post',
        'content' => '<p>Full body.</p>',
    ]);
    $post->seoMeta()->create([
        'meta_title' => 'Custom Meta',
        'meta_description' => 'Custom Description',
    ]);
    Comment::factory()->approved()->create(['post_id' => $post->id, 'content' => 'Approved comment']);
    Comment::factory()->pending()->create(['post_id' => $post->id, 'content' => 'Pending comment']);
    Comment::factory()->spam()->create(['post_id' => $post->id, 'content' => 'Spam comment']);

    $response = $this->getJson(route('api.v1.posts.show', $post->slug))
        ->assertOk()
        ->assertJsonPath('data.title', 'Detail Post')
        ->assertJsonPath('data.content', '<p>Full body.</p>')
        ->assertJsonPath('data.seo.meta_title', 'Custom Meta')
        ->assertJsonCount(1, 'data.comments')
        ->assertJsonPath('data.comments.0.content', 'Approved comment');

    expect($response->json('data.comments.0'))->not->toHaveKey('author_email');
});

test('approved replies are nested under their parent comment', function () {
    $post = Post::factory()->published()->create();
    $parent = Comment::factory()->approved()->create(['post_id' => $post->id, 'content' => 'Parent']);
    Comment::factory()->approved()->create(['post_id' => $post->id, 'parent_id' => $parent->id, 'content' => 'Approved reply']);
    Comment::factory()->pending()->create(['post_id' => $post->id, 'parent_id' => $parent->id, 'content' => 'Pending reply']);

    $this->getJson(route('api.v1.posts.show', $post->slug))
        ->assertOk()
        ->assertJsonCount(1, 'data.comments')
        ->assertJsonCount(1, 'data.comments.0.replies')
        ->assertJsonPath('data.comments.0.replies.0.content', 'Approved reply');
});

test('unpublished posts 404 on the detail endpoint', function () {
    $draft = Post::factory()->draft()->create();
    $scheduled = Post::factory()->scheduled()->create();

    $this->getJson(route('api.v1.posts.show', $draft->slug))->assertNotFound();
    $this->getJson(route('api.v1.posts.show', $scheduled->slug))->assertNotFound();
});

test('the categories endpoint counts only published posts', function () {
    $category = Category::factory()->create(['name' => 'Counted']);
    $published = Post::factory()->published()->create();
    $draft = Post::factory()->draft()->create();
    $published->categories()->sync([$category->id]);
    $draft->categories()->sync([$category->id]);

    $this->getJson(route('api.v1.categories.index'))
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Counted')
        ->assertJsonPath('data.0.posts_count', 1);
});

test('the tags endpoint lists tags with slugs', function () {
    Tag::factory()->create(['name' => 'Listed Tag']);

    $this->getJson(route('api.v1.tags.index'))
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Listed Tag');
});

test('a guest can submit a comment which lands as pending', function () {
    $post = Post::factory()->published()->create();

    $this->postJson(route('api.v1.posts.comments.store', $post->slug), [
        'author_name' => 'Reader',
        'author_email' => 'reader@example.com',
        'content' => 'Great post!',
    ])->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonStructure(['data' => ['id', 'status']]);

    $comment = Comment::firstWhere('content', 'Great post!');

    expect($comment)->not->toBeNull()
        ->and($comment->status)->toBe(CommentStatus::Pending)
        ->and($comment->author_name)->toBe('Reader');
});

test('guest comments require name and email', function () {
    $post = Post::factory()->published()->create();

    $this->postJson(route('api.v1.posts.comments.store', $post->slug), [
        'content' => 'Anonymous drive-by',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['author_name', 'author_email']);
});

test('the honeypot field rejects bot submissions', function () {
    $post = Post::factory()->published()->create();

    $this->postJson(route('api.v1.posts.comments.store', $post->slug), [
        'author_name' => 'Bot',
        'author_email' => 'bot@example.com',
        'content' => 'Buy now!',
        'website' => 'https://spam.example.com',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['website']);
});

test('comments cannot be posted to unpublished posts', function () {
    $draft = Post::factory()->draft()->create();

    $this->postJson(route('api.v1.posts.comments.store', $draft->slug), [
        'author_name' => 'Reader',
        'author_email' => 'reader@example.com',
        'content' => 'Hello?',
    ])->assertNotFound();
});

test('a reply must target a comment on the same post', function () {
    $post = Post::factory()->published()->create();
    $otherPost = Post::factory()->published()->create();
    $foreignComment = Comment::factory()->approved()->create(['post_id' => $otherPost->id]);

    $this->postJson(route('api.v1.posts.comments.store', $post->slug), [
        'author_name' => 'Reader',
        'author_email' => 'reader@example.com',
        'content' => 'Cross-post reply',
        'parent_id' => $foreignComment->id,
    ])->assertUnprocessable();
});

test('the post detail embeds BlogPosting JSON-LD with dates and publisher', function () {
    $post = Post::factory()->published()->create();

    $response = $this->getJson(route('api.v1.posts.show', $post->slug))->assertOk();

    $jsonLd = collect($response->json('data.seo.json_ld'))->firstWhere('@type', 'BlogPosting');

    expect($jsonLd)->not->toBeNull()
        ->and($jsonLd['datePublished'])->not->toBeNull()
        ->and($jsonLd['publisher']['name'])->toBe(config('app.name'))
        ->and($jsonLd['mainEntityOfPage'])->toContain($post->slug);
});

test('the post detail exposes published-order neighbours and related posts', function () {
    $category = Category::factory()->create();

    $first = Post::factory()->published()->create(['published_at' => now()->subDays(3)]);
    $middle = Post::factory()->published()->create(['published_at' => now()->subDays(2)]);
    $last = Post::factory()->published()->create(['published_at' => now()->subDay()]);

    foreach ([$first, $middle, $last] as $post) {
        $post->categories()->sync([$category->id]);
    }

    $this->getJson(route('api.v1.posts.show', $middle->slug))
        ->assertOk()
        ->assertJsonPath('data.previous_post.slug', $first->slug)
        ->assertJsonPath('data.next_post.slug', $last->slug)
        ->assertJsonCount(2, 'data.related_posts');
});
