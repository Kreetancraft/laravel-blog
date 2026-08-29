<?php

use Illuminate\Support\Facades\Route;
use Kreetancraft\Blog\Http\Controllers\Api\BlogController;

/*
 * Public, read-only endpoints. Only published posts are exposed. Comment
 * submission always lands in the moderation queue as pending.
 *
 * Rate limiters are configurable and unset by default — naming one the host has
 * not defined would throw on routes this package registers automatically.
 */
$names = config('blog.routes.names', []);

$read = config('blog.routes.api_rate_limiter');
$write = config('blog.routes.api_write_rate_limiter');

Route::prefix('v1')->name('api.v1.')->group(function () use ($read, $write): void {
    $readRoutes = function (): void {
        Route::get('posts', [BlogController::class, 'index'])->name('posts.index');
        Route::get('posts/{slug}', [BlogController::class, 'show'])->name('posts.show');
        Route::get('categories', [BlogController::class, 'categories'])->name('categories.index');
        Route::get('categories/{slug}', [BlogController::class, 'showCategory'])->name('categories.show');
        Route::get('authors/{slug}', [BlogController::class, 'showAuthor'])->name('authors.show');
        Route::get('series/{slug}', [BlogController::class, 'showSeries'])->name('series.show');
        Route::get('tags', [BlogController::class, 'tags'])->name('tags.index');
    };

    $read ? Route::middleware('throttle:'.$read)->group($readRoutes) : $readRoutes();

    if (config('blog.comments.enabled', true)) {
        $writeRoutes = function (): void {
            Route::post('posts/{slug}/comments', [BlogController::class, 'storeComment'])
                ->name('posts.comments.store');
        };

        $write ? Route::middleware('throttle:'.$write)->group($writeRoutes) : $writeRoutes();
    }
});
