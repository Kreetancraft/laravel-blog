<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Two independent switches. The admin screens and the public read API are
    | separate: a host may serve the API while replacing the admin UI entirely,
    | or the reverse.
    |
    */
    'routes' => [
        'register' => true,
        'prefix' => 'admin',
        'middleware' => ['web', 'auth'],

        // The editor bundle. Separate from the admin screens: a host that
        // replaces the UI does not need it, and one that keeps the UI does.
        'serve_assets' => true,
        'asset_middleware' => ['web'],

        'register_api' => true,
        'api_prefix' => 'api',
        'api_middleware' => ['api'],

        // Named rate limiters for the public API. Unset by default: naming one
        // the host has not defined throws on a route registered automatically.
        'api_rate_limiter' => null,
        'api_write_rate_limiter' => null,

        // Where the "Dashboard" breadcrumb points: a route name or a URL.
        'home' => 'dashboard',

        'names' => [
            'posts' => 'admin.blog.posts',
            'posts.create' => 'admin.blog.posts.create',
            'posts.edit' => 'admin.blog.posts.edit',
            'categories' => 'admin.blog.categories',
            'tags' => 'admin.blog.tags',
            'authors' => 'admin.blog.authors',
            'authors.create' => 'admin.blog.authors.create',
            'authors.edit' => 'admin.blog.authors.edit',
            'series' => 'admin.blog.series',
            'series.create' => 'admin.blog.series.create',
            'series.edit' => 'admin.blog.series.edit',
            'comments' => 'admin.blog.comments',
            'comments.show' => 'admin.blog.comments.show',
            'asset' => 'blog.asset.editor',
        ],
    ],

    'layouts' => [
        'admin' => 'components.layouts.app',
    ],

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    |
    | This package ships no image handling. Point `image_resolver` at a class
    | that can turn a model plus a collection name into URLs and featured
    | images, author avatars and series covers resolve from your media library;
    | leave it null and they return null and their pickers are hidden.
    |
    |     'image_resolver' => \Kreetancraft\Media\Support\MediaImageResolver::class,
    |
    */
    'image_resolver' => null,

    // Your own view for picking images, rendered where a picker belongs and
    // given $items (already resolved) and $group.
    'media_picker_view' => null,

    'collections' => [
        'featured' => 'featured',
        'author_avatar' => 'avatar',
        'series_cover' => 'cover',
    ],

    /*
    |--------------------------------------------------------------------------
    | Public API
    |--------------------------------------------------------------------------
    */
    'per_page' => 12,

    'cache_ttl' => 300,

    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    |
    | Submitted comments always land in the moderation queue as pending.
    |
    */
    'comments' => [
        'enabled' => true,
    ],
];
