<?php

use Illuminate\Support\Facades\Route;
use Kreetancraft\Blog\Livewire\CreateCategory;
use Kreetancraft\Blog\Livewire\CreateTag;
use Kreetancraft\Blog\Livewire\EditCategory;
use Kreetancraft\Blog\Livewire\EditTag;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Tag;
use Livewire\Livewire;

/**
 * Dedicated create and edit pages for categories and tags.
 *
 * These replaced the modals the index screens used to open. A category carries
 * the full SEO panel — meta, Open Graph, Twitter and three live previews — which
 * does not fit a modal, and keeping both would have left two edit paths free to
 * diverge. The listings keep delete, where the row is.
 */
beforeEach(function (): void {
    $this->actingAs(actingAsBlogManager());
});

it('creates a category with its seo meta in one transaction', function (): void {
    Livewire::test(CreateCategory::class)
        ->set('name', 'Trekking')
        ->set('description', 'Long walks uphill.')
        ->set('seo_meta_title', 'Trekking guides')
        ->call('save')
        ->assertHasNoErrors();

    $category = Category::where('name', 'Trekking')->first();

    expect($category)->not->toBeNull()
        ->and($category->slug)->toBe('trekking')
        ->and($category->seoMeta->meta_title)->toBe('Trekking guides');
});

it('edits a category and keeps its meta', function (): void {
    $category = Category::create(['name' => 'Old name']);

    Livewire::test(EditCategory::class, ['category' => $category])
        ->assertSet('name', 'Old name')
        ->set('name', 'New name')
        ->set('seo_meta_description', 'Rewritten.')
        ->call('save')
        ->assertHasNoErrors();

    expect($category->fresh()->name)->toBe('New name')
        ->and($category->fresh()->seoMeta->meta_description)->toBe('Rewritten.');
});

it('deletes a category from its own page', function (): void {
    $category = Category::create(['name' => 'Doomed']);

    Livewire::test(EditCategory::class, ['category' => $category])->call('delete');

    expect(Category::find($category->id))->toBeNull();
});

it('rejects a duplicate category name but allows the record to keep its own', function (): void {
    Category::create(['name' => 'Taken']);
    $mine = Category::create(['name' => 'Mine']);

    Livewire::test(CreateCategory::class)
        ->set('name', 'Taken')
        ->call('save')
        ->assertHasErrors(['name']);

    // Saving a record without renaming it must not trip its own unique rule.
    Livewire::test(EditCategory::class, ['category' => $mine])
        ->call('save')
        ->assertHasNoErrors();
});

it('creates and edits a tag', function (): void {
    Livewire::test(CreateTag::class)
        ->set('name', 'Gear')
        ->call('save')
        ->assertHasNoErrors();

    $tag = Tag::where('name', 'Gear')->firstOrFail();

    Livewire::test(EditTag::class, ['tag' => $tag])
        ->assertSet('name', 'Gear')
        ->set('name', 'Equipment')
        ->call('save')
        ->assertHasNoErrors();

    expect($tag->fresh()->name)->toBe('Equipment');
});

it('refuses the create pages to someone without the permission', function (): void {
    $this->actingAs(actingAsOutsider());

    Livewire::test(CreateCategory::class)->assertForbidden();
    Livewire::test(CreateTag::class)->assertForbidden();
});

it('gates each new route on the matching policy ability', function (): void {
    $expected = [
        'admin.blog.categories.create' => 'can:create,'.Category::class,
        'admin.blog.categories.edit' => 'can:update,'.Category::class,
        'admin.blog.tags.create' => 'can:create,'.Tag::class,
        'admin.blog.tags.edit' => 'can:update,'.Tag::class,
    ];

    foreach ($expected as $name => $middleware) {
        $route = Route::getRoutes()->getByName($name);

        expect($route)->not->toBeNull("route {$name} is not registered")
            ->and($route->gatherMiddleware())->toContain($middleware);
    }
});

it('declares the create route before the parameterised one', function (): void {
    // `categories/create` must not be swallowed by `categories/{category}/edit`.
    $create = Route::getRoutes()->getByName('admin.blog.categories.create');

    expect($create->uri())->toBe('admin/blog/categories/create');
});
