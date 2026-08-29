<?php

use Kreetancraft\Blog\Livewire\CreateCategory;
use Kreetancraft\Blog\Livewire\CreateTag;
use Kreetancraft\Blog\Livewire\EditCategory;
use Kreetancraft\Blog\Livewire\EditTag;
use Kreetancraft\Blog\Livewire\ManageCategories;
use Kreetancraft\Blog\Livewire\ManageTags;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Tag;
use Livewire\Livewire;

/*
 * Creating and editing moved to their own pages in 0.4.0: a category carries
 * the full SEO panel, which does not fit a modal. The listing keeps delete.
 */
test('a package manager can create a category', function () {
    actingAsBlogManager();

    Livewire::test(CreateCategory::class)
        ->set('name', 'Trail Reports')
        ->set('description', 'Season by season conditions.')
        ->call('save')
        ->assertHasNoErrors();

    $category = Category::firstWhere('name', 'Trail Reports');

    expect($category)->not->toBeNull()
        ->and($category->slug)->toBe('trail-reports');
});

test('category names are unique', function () {
    actingAsBlogManager();
    Category::factory()->create(['name' => 'Dupe Category']);

    Livewire::test(CreateCategory::class)
        ->set('name', 'Dupe Category')
        ->call('save')
        ->assertHasErrors(['name']);
});

test('a package manager can edit a category', function () {
    actingAsBlogManager();
    $category = Category::factory()->create(['name' => 'Before']);

    Livewire::test(EditCategory::class, ['category' => $category])
        ->set('name', 'After')
        ->call('save')
        ->assertHasNoErrors();

    expect($category->fresh()->name)->toBe('After');
});

test('deleting a category soft-deletes it and keeps posts', function () {
    actingAsBlogManager();
    $category = Category::factory()->create();
    $post = Post::factory()->create();
    $post->categories()->sync([$category->id]);

    Livewire::test(ManageCategories::class)
        ->call('delete', $category->id);

    expect(Category::find($category->id))->toBeNull()
        ->and(Category::withTrashed()->find($category->id))->not->toBeNull()
        ->and(Post::find($post->id))->not->toBeNull();
});

test('a package manager can create and edit a tag', function () {
    actingAsBlogManager();

    Livewire::test(CreateTag::class)
        ->set('name', 'Gear')
        ->call('save')
        ->assertHasNoErrors();

    $tag = Tag::firstWhere('name', 'Gear');
    expect($tag)->not->toBeNull()->and($tag->slug)->toBe('gear');

    Livewire::test(EditTag::class, ['tag' => $tag])
        ->set('name', 'Equipment')
        ->call('save')
        ->assertHasNoErrors();

    expect($tag->fresh()->name)->toBe('Equipment');
});

test('deleting a tag removes it entirely', function () {
    actingAsBlogManager();
    $tag = Tag::factory()->create();

    Livewire::test(ManageTags::class)
        ->call('delete', $tag->id);

    expect(Tag::find($tag->id))->toBeNull();
});

test('a role without view-blogs cannot open the taxonomy pages', function () {
    actingAsOutsider();

    $this->get(route('admin.blog.categories'))->assertForbidden();
    $this->get(route('admin.blog.tags'))->assertForbidden();
});
