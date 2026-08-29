<?php

use Kreetancraft\Blog\Enums\CommentStatus;
use Kreetancraft\Blog\Livewire\ManageComments;
use Kreetancraft\Blog\Models\Comment;
use Kreetancraft\Blog\Models\Post;
use Livewire\Livewire;

test('a role without moderate-blog-comments cannot reach the queue', function () {
    actingAsOutsider();

    $this->get(route('admin.blog.comments'))->assertForbidden();
});

test('the queue defaults to pending comments', function () {
    actingAsBlogManager();
    $post = Post::factory()->create();
    Comment::factory()->pending()->create(['post_id' => $post->id, 'content' => 'Waiting for review']);
    Comment::factory()->approved()->create(['post_id' => $post->id, 'content' => 'Already live']);

    Livewire::test(ManageComments::class)
        ->assertSee('Waiting for review')
        ->assertDontSee('Already live');
});

test('a moderator can approve a single comment', function () {
    actingAsBlogManager();
    $comment = Comment::factory()->pending()->create();

    Livewire::test(ManageComments::class)
        ->call('setStatus', $comment->id, 'approved');

    expect($comment->fresh()->status)->toBe(CommentStatus::Approved);
});

test('a moderator can bulk-moderate comments', function () {
    actingAsBlogManager();
    $post = Post::factory()->create();
    $comments = Comment::factory()->pending()->count(3)->create(['post_id' => $post->id]);

    Livewire::test(ManageComments::class)
        ->set('selected', $comments->pluck('id')->all())
        ->call('bulkSetStatus', 'spam');

    expect(Comment::where('status', CommentStatus::Spam->value)->count())->toBe(3);
});

test('a moderator can bulk-delete comments', function () {
    actingAsBlogManager();
    $post = Post::factory()->create();
    $comments = Comment::factory()->pending()->count(2)->create(['post_id' => $post->id]);

    Livewire::test(ManageComments::class)
        ->set('selected', $comments->pluck('id')->all())
        ->call('bulkDelete');

    expect(Comment::count())->toBe(0);
});

test('the queue searches author name, email, and content', function () {
    actingAsBlogManager();
    $post = Post::factory()->create();
    Comment::factory()->pending()->create(['post_id' => $post->id, 'author_name' => 'Alice Zephyr', 'content' => 'First comment']);
    Comment::factory()->pending()->create(['post_id' => $post->id, 'author_name' => 'Bob Quill', 'content' => 'Second comment']);

    Livewire::test(ManageComments::class)
        ->set('search', 'Zephyr')
        ->assertSee('First comment')
        ->assertDontSee('Second comment');
});

test('deleting a comment removes it', function () {
    actingAsBlogManager();
    $comment = Comment::factory()->pending()->create();

    Livewire::test(ManageComments::class)
        ->call('delete', $comment->id);

    expect(Comment::find($comment->id))->toBeNull();
});

use Kreetancraft\Blog\Livewire\ViewComment;

test('a moderator can reply to a comment', function () {
    $moderator = actingAsBlogManager();
    $comment = Comment::factory()->approved()->create();

    Livewire::test(ViewComment::class, ['comment' => $comment])
        ->set('replyContent', 'Thanks for reading!')
        ->call('sendReply')
        ->assertHasNoErrors();

    $reply = Comment::firstWhere('content', 'Thanks for reading!');

    expect($reply)->not->toBeNull()
        ->and($reply->post_id)->toBe($comment->post_id)
        ->and($reply->parent_id)->toBe($comment->id)
        ->and($reply->user_id)->toBe($moderator->id)
        ->and($reply->status)->toBe(CommentStatus::Approved)
        ->and($reply->displayName())->toBe($moderator->name);
});

test('replying to a pending comment approves it', function () {
    actingAsBlogManager();
    $comment = Comment::factory()->pending()->create();

    Livewire::test(ViewComment::class, ['comment' => $comment])
        ->set('replyContent', 'Good question — yes.')
        ->call('sendReply')
        ->assertHasNoErrors();

    expect($comment->fresh()->status)->toBe(CommentStatus::Approved);
});

test('replying to a reply anchors to the thread root', function () {
    actingAsBlogManager();
    $root = Comment::factory()->approved()->create();
    $reply = Comment::factory()->approved()->create([
        'post_id' => $root->post_id,
        'parent_id' => $root->id,
    ]);

    Livewire::test(ViewComment::class, ['comment' => $reply])
        ->set('replyContent', 'Nested answer.')
        ->call('sendReply')
        ->assertHasNoErrors();

    expect(Comment::firstWhere('content', 'Nested answer.')->parent_id)->toBe($root->id);
});

test('a reply requires content', function () {
    actingAsBlogManager();
    $comment = Comment::factory()->approved()->create();

    Livewire::test(ViewComment::class, ['comment' => $comment])
        ->set('replyContent', '')
        ->call('sendReply')
        ->assertHasErrors(['replyContent']);
});

test('the queue filters by post', function () {
    actingAsBlogManager();
    $postA = Post::factory()->create();
    $postB = Post::factory()->create();
    Comment::factory()->pending()->create(['post_id' => $postA->id, 'content' => 'On post A']);
    Comment::factory()->pending()->create(['post_id' => $postB->id, 'content' => 'On post B']);

    Livewire::test(ManageComments::class)
        ->set('postFilter', (string) $postA->id)
        ->assertSee('On post A')
        ->assertDontSee('On post B');
});

test('status tabs switch the visible comments', function () {
    actingAsBlogManager();
    $post = Post::factory()->create();
    Comment::factory()->pending()->create(['post_id' => $post->id, 'content' => 'Pending one']);
    Comment::factory()->spam()->create(['post_id' => $post->id, 'content' => 'Spam one']);

    Livewire::test(ManageComments::class)
        ->call('setStatusTab', 'spam')
        ->assertSee('Spam one')
        ->assertDontSee('Pending one')
        ->call('setStatusTab', '')
        ->assertSee('Spam one')
        ->assertSee('Pending one');
});

test('select page selects every comment on the current page', function () {
    actingAsBlogManager();
    $post = Post::factory()->create();
    $comments = Comment::factory()->pending()->count(3)->create(['post_id' => $post->id]);

    $expected = $comments->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all();

    Livewire::test(ManageComments::class)
        ->set('selectPage', true)
        ->assertSet('selected', fn (array $selected): bool => collect($selected)->sort()->values()->all() === $expected)
        ->set('selectPage', false)
        ->assertSet('selected', []);
});

test('the detail view shows the full thread', function () {
    actingAsBlogManager();
    $parent = Comment::factory()->approved()->create(['content' => 'Root comment full text']);
    Comment::factory()->approved()->create([
        'post_id' => $parent->post_id,
        'parent_id' => $parent->id,
        'content' => 'A nested reply',
    ]);

    Livewire::test(ViewComment::class, ['comment' => $parent])
        ->assertSee('Root comment full text')
        ->assertSee('A nested reply');
});

test('deleting the viewed comment redirects to the queue', function () {
    actingAsBlogManager();
    $comment = Comment::factory()->pending()->create();

    Livewire::test(ViewComment::class, ['comment' => $comment])
        ->call('delete')
        ->assertRedirect(route('admin.blog.comments'));

    expect(Comment::find($comment->id))->toBeNull();
});

test('team replies expose the author name on the public API', function () {
    $moderator = actingAsBlogManager();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->approved()->create(['post_id' => $post->id]);

    Livewire::test(ViewComment::class, ['comment' => $comment])
        ->set('replyContent', 'Official answer.')
        ->call('sendReply');

    auth()->logout();

    $this->getJson(route('api.v1.posts.show', $post->slug))
        ->assertOk()
        ->assertJsonPath('data.comments.0.replies.0.content', 'Official answer.')
        ->assertJsonPath('data.comments.0.replies.0.author_name', $moderator->name);
});
