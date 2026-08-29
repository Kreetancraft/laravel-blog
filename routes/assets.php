<?php

use Illuminate\Support\Facades\Route;
use Kreetancraft\Blog\Http\Controllers\AssetController;

/*
 * The editor bundle. Public and unauthenticated because it is a static asset,
 * and served from the package so upgrading is `composer update` alone.
 */
Route::get('blog/editor.js', [AssetController::class, 'editor'])
    ->name(config('blog.routes.names.asset', 'blog.asset.editor'));
