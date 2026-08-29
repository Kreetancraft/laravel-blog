<?php

namespace Kreetancraft\Blog\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Versioned cache for the public blog API. Every cached key embeds a single
 * integer version; bumping it (on any post/category/tag write) invalidates the
 * whole blog surface in O(1) — the `database` cache store has no tags.
 */
class BlogApiCache
{
    private const VERSION_KEY = 'blog:api:version';

    /**
     * Shared public-API response cache TTL (seconds).
     */
    public static function ttl(): int
    {
        return (int) config('api.cache.ttl', 300);
    }

    public static function version(): int
    {
        return (int) (Cache::get(self::VERSION_KEY) ?? 1);
    }

    public static function key(string $suffix): string
    {
        return 'blog:api:v'.self::version().':'.$suffix;
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }
}
