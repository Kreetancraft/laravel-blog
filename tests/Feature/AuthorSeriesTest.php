<?php

use Kreetancraft\Blog\Enums\SeriesStatus;
use Kreetancraft\Blog\Livewire\CreateAuthor;
use Kreetancraft\Blog\Livewire\CreateSeries;
use Kreetancraft\Blog\Livewire\EditAuthor;
use Kreetancraft\Blog\Livewire\EditSeries;
use Kreetancraft\Blog\Livewire\ManageAuthors;
use Kreetancraft\Blog\Livewire\ManageSeries;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Series;
use Livewire\Livewire;

test('a package manager can create an author', function () {
    actingAsBlogManager();

    Livewire::test(CreateAuthor::class)
        ->set('name', 'Pemba Sherpa')
        ->set('bio', '<p>Guide.</p>')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $author = Author::firstWhere('name', 'Pemba Sherpa');

    expect($author)->not->toBeNull()
        ->and($author->slug)->toBe('pemba-sherpa');
});

test('the media-picked event updates the author avatar selection', function () {
    actingAsBlogManager();
    $author = Author::factory()->create();

    Livewire::test(EditAuthor::class, ['author' => $author])
        ->call('onMediaPicked', [7], 'blog-author-avatar', [
            ['id' => 7, 'url' => '/assets/avatar.jpg', 'name' => 'avatar.jpg'],
        ])
        ->assertSet('avatarMedia', [
            ['id' => 7, 'url' => '/assets/avatar.jpg', 'name' => 'avatar.jpg'],
        ]);
});

test('media-picked events for other groups are ignored', function () {
    actingAsBlogManager();
    $author = Author::factory()->create();

    Livewire::test(EditAuthor::class, ['author' => $author])
        ->call('onMediaPicked', [7], 'something-else', [
            ['id' => 7, 'url' => '/assets/x.jpg', 'name' => 'x.jpg'],
        ])
        ->assertSet('avatarMedia', []);
});

test('an author with posts cannot be deleted', function () {
    actingAsBlogManager();
    $author = Author::factory()->create();
    Post::factory()->create(['author_id' => $author->id]);

    Livewire::test(ManageAuthors::class)
        ->call('delete', $author->id);

    expect(Author::find($author->id))->not->toBeNull();
});

test('an author without posts can be deleted', function () {
    actingAsBlogManager();
    $author = Author::factory()->create();

    Livewire::test(ManageAuthors::class)
        ->call('delete', $author->id);

    expect(Author::find($author->id))->toBeNull();
});

test('a package manager can create a series', function () {
    actingAsBlogManager();

    Livewire::test(CreateSeries::class)
        ->set('title', 'EBC Prep')
        ->set('status', 'active')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $series = Series::firstWhere('title', 'EBC Prep');

    expect($series)->not->toBeNull()
        ->and($series->status)->toBe(SeriesStatus::Active)
        ->and($series->slug)->toBe('ebc-prep');
});

test('a package manager can edit a series and see its posts', function () {
    actingAsBlogManager();
    $series = Series::factory()->create(['title' => 'Old Series']);
    Post::factory()->create(['series_id' => $series->id, 'order_in_series' => 1, 'title' => 'Part One']);

    Livewire::test(EditSeries::class, ['series' => $series])
        ->assertSee('Part One')
        ->set('title', 'New Series')
        ->call('save')
        ->assertHasNoErrors();

    expect($series->fresh()->title)->toBe('New Series');
});

test('deleting a series keeps its posts but clears series_id', function () {
    actingAsBlogManager();
    $series = Series::factory()->create();
    $post = Post::factory()->create(['series_id' => $series->id]);

    Livewire::test(ManageSeries::class)
        ->call('delete', $series->id);

    expect(Series::find($series->id))->toBeNull()
        ->and($post->fresh()->series_id)->toBeNull();
});
