<?php

use Kreetancraft\Blog\Actions\PublishScheduledPostsAction;
use Kreetancraft\Blog\Enums\PostStatus;
use Kreetancraft\Blog\Models\Post;

test('due scheduled posts are published and keep their publish date', function () {
    $due = Post::factory()->create([
        'status' => PostStatus::Scheduled,
        'published_at' => now()->subMinutes(5),
    ]);

    $count = PublishScheduledPostsAction::run();

    expect($count)->toBe(1)
        ->and($due->fresh()->status)->toBe(PostStatus::Published)
        ->and($due->fresh()->published_at->timestamp)->toBe($due->published_at->timestamp);
});

test('future scheduled and draft posts are left alone', function () {
    $future = Post::factory()->scheduled()->create();
    $draft = Post::factory()->draft()->create();

    $count = PublishScheduledPostsAction::run();

    expect($count)->toBe(0)
        ->and($future->fresh()->status)->toBe(PostStatus::Scheduled)
        ->and($draft->fresh()->status)->toBe(PostStatus::Draft);
});

test('the artisan command publishes due posts and exposes them on the API', function () {
    $due = Post::factory()->create([
        'status' => PostStatus::Scheduled,
        'published_at' => now()->subMinute(),
        'title' => 'Now Live',
    ]);

    $this->artisan('blogs:publish-scheduled')
        ->expectsOutputToContain('Published 1')
        ->assertSuccessful();

    $this->getJson(route('api.v1.posts.index'))
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Now Live');
});
