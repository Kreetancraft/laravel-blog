<?php

use Illuminate\Support\Facades\Route;
use Kreetancraft\Blog\Livewire\CreateAuthor;
use Kreetancraft\Blog\Livewire\CreateCategory;
use Kreetancraft\Blog\Livewire\CreatePost;
use Kreetancraft\Blog\Livewire\CreateSeries;
use Kreetancraft\Blog\Livewire\CreateTag;
use Kreetancraft\Blog\Livewire\EditAuthor;
use Kreetancraft\Blog\Livewire\EditCategory;
use Kreetancraft\Blog\Livewire\EditPost;
use Kreetancraft\Blog\Livewire\EditSeries;
use Kreetancraft\Blog\Livewire\EditTag;
use Kreetancraft\Blog\Livewire\ManageAuthors;
use Kreetancraft\Blog\Livewire\ManageCategories;
use Kreetancraft\Blog\Livewire\ManageComments;
use Kreetancraft\Blog\Livewire\ManagePosts;
use Kreetancraft\Blog\Livewire\ManageSeries;
use Kreetancraft\Blog\Livewire\ManageTags;
use Kreetancraft\Blog\Livewire\ViewComment;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Comment;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Blog\Models\Tag;

/*
 * Admin screens. Prefix and middleware come from config via BlogServiceProvider;
 * this file only declares paths and names.
 *
 * Gates are the ordinary policy form — ability plus model — not permission
 * strings. This package names no permission; whatever policy is bound to each
 * model decides, and a host can replace any of them wholesale.
 *
 * Literal segments are declared before parameterised ones so `posts/create`
 * cannot be swallowed by `posts/{post}`.
 */
$names = config('blog.routes.names', []);

Route::middleware('can:create,'.Post::class)->group(function () use ($names): void {
    Route::get('blog/posts/create', CreatePost::class)
        ->name($names['posts.create'] ?? 'admin.blog.posts.create');
});

Route::middleware('can:create,'.Author::class)->group(function () use ($names): void {
    Route::get('blog/authors/create', CreateAuthor::class)
        ->name($names['authors.create'] ?? 'admin.blog.authors.create');
});

Route::middleware('can:create,'.Series::class)->group(function () use ($names): void {
    Route::get('blog/series/create', CreateSeries::class)
        ->name($names['series.create'] ?? 'admin.blog.series.create');
});

Route::middleware('can:update,'.Post::class)->group(function () use ($names): void {
    Route::get('blog/posts/{post}/edit', EditPost::class)
        ->name($names['posts.edit'] ?? 'admin.blog.posts.edit')
        ->whereNumber('post');
});

Route::middleware('can:update,'.Author::class)->group(function () use ($names): void {
    Route::get('blog/authors/{author}/edit', EditAuthor::class)
        ->name($names['authors.edit'] ?? 'admin.blog.authors.edit')
        ->whereNumber('author');
});

Route::middleware('can:update,'.Series::class)->group(function () use ($names): void {
    Route::get('blog/series/{series}/edit', EditSeries::class)
        ->name($names['series.edit'] ?? 'admin.blog.series.edit')
        ->whereNumber('series');
});

Route::middleware('can:create,'.Category::class)->group(function () use ($names): void {
    Route::get('blog/categories/create', CreateCategory::class)
        ->name($names['categories.create'] ?? 'admin.blog.categories.create');
});

Route::middleware('can:create,'.Tag::class)->group(function () use ($names): void {
    Route::get('blog/tags/create', CreateTag::class)
        ->name($names['tags.create'] ?? 'admin.blog.tags.create');
});

Route::middleware('can:update,'.Category::class)->group(function () use ($names): void {
    Route::get('blog/categories/{category}/edit', EditCategory::class)
        ->name($names['categories.edit'] ?? 'admin.blog.categories.edit')
        ->whereNumber('category');
});

Route::middleware('can:update,'.Tag::class)->group(function () use ($names): void {
    Route::get('blog/tags/{tag}/edit', EditTag::class)
        ->name($names['tags.edit'] ?? 'admin.blog.tags.edit')
        ->whereNumber('tag');
});

Route::middleware('can:viewAny,'.Post::class)->group(function () use ($names): void {
    Route::get('blog/posts', ManagePosts::class)
        ->name($names['posts'] ?? 'admin.blog.posts');
});

Route::middleware('can:viewAny,'.Category::class)->group(function () use ($names): void {
    Route::get('blog/categories', ManageCategories::class)
        ->name($names['categories'] ?? 'admin.blog.categories');
});

Route::middleware('can:viewAny,'.Tag::class)->group(function () use ($names): void {
    Route::get('blog/tags', ManageTags::class)
        ->name($names['tags'] ?? 'admin.blog.tags');
});

Route::middleware('can:viewAny,'.Author::class)->group(function () use ($names): void {
    Route::get('blog/authors', ManageAuthors::class)
        ->name($names['authors'] ?? 'admin.blog.authors');
});

Route::middleware('can:viewAny,'.Series::class)->group(function () use ($names): void {
    Route::get('blog/series', ManageSeries::class)
        ->name($names['series'] ?? 'admin.blog.series');
});

Route::middleware('can:viewAny,'.Comment::class)->group(function () use ($names): void {
    Route::get('blog/comments', ManageComments::class)
        ->name($names['comments'] ?? 'admin.blog.comments');

    Route::get('blog/comments/{comment}', ViewComment::class)
        ->name($names['comments.show'] ?? 'admin.blog.comments.show')
        ->whereNumber('comment');
});
