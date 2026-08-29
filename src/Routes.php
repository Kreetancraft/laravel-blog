<?php

namespace Kreetancraft\Blog;

use Illuminate\Support\Facades\Route;

/**
 * Route names, read from config rather than written into twenty views.
 *
 * A host that mounts these screens somewhere else only has to say so once, in
 * `blog.routes.names`. Hardcoding `admin.blog.posts` across the views is what
 * made the module unmovable.
 */
class Routes
{
    /**
     * Sensible defaults, used when a key is not configured.
     *
     * @var array<string, string>
     */
    private const DEFAULTS = [
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
    ];

    /**
     * The configured route name for a key.
     */
    public static function name(string $key): string
    {
        return (string) config('blog.routes.names.'.$key, self::DEFAULTS[$key] ?? $key);
    }

    /**
     * A URL for one of this package's screens.
     *
     * Returns '#' when the route does not exist, so a view cannot fatal because
     * the admin routes were switched off in config — the link is simply dead.
     *
     * @param  mixed  $parameters
     */
    public static function to(string $key, $parameters = []): string
    {
        $name = self::name($key);

        return Route::has($name)
            ? route($name, $parameters)
            : '#';
    }
}
