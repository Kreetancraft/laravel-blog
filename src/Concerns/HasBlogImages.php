<?php

namespace Kreetancraft\Blog\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Kreetancraft\Seo\Support\ImageResolver;

/**
 * Images for a blog model, when something is installed that can supply them.
 *
 * This package ships no image handling. The models used to `use` a media
 * package's trait, which made that package a hard dependency: PHP cannot
 * conditionally apply a trait, so a missing one is a fatal error rather than a
 * missing feature.
 *
 * So they ask here instead, and here asks whatever `blog.image_resolver` names.
 * With nothing configured every method returns empty, the admin pickers hide
 * themselves, and the API returns null images — the blog works, it just has no
 * pictures.
 */
trait HasBlogImages
{
    /**
     * URL of the first image in a collection, or null.
     */
    public function imageUrl(string $collection): ?string
    {
        return static::blogImages()->url($this, $collection);
    }

    /**
     * Every image in a collection, shaped for a picker.
     *
     * @return list<array{id: int|string, url: ?string, name: ?string}>
     */
    public function imageList(string $collection): array
    {
        return static::blogImages()->list($this, $collection);
    }

    /**
     * Attach exactly these images to a collection, detaching the rest.
     *
     * Named as the media package's trait named it, so the actions that call it
     * did not have to change when the trait was replaced by this seam.
     *
     * @param  list<int|string>  $ids
     */
    public function syncAttachedMedia(array $ids, string $collection = 'default'): void
    {
        static::blogImages()->sync($this, $collection, $ids);
    }

    /**
     * Warm a whole page of models in one query.
     *
     * Resolving per model is an N+1 by construction — two extra queries a row
     * on a listing. Repositories call this once per page so the resolver can
     * fetch the lot with a single `whereIn`.
     *
     * @param  Collection<int, static>|iterable<static>  $models
     */
    public static function preloadImages(iterable $models, string $collection): void
    {
        static::blogImages()->preload($models, $collection);
    }

    /**
     * Whether an image resolver is configured at all.
     *
     * The admin screens read this to decide whether to render a picker: one
     * with nothing behind it is worse than none.
     */
    public static function imagesEnabled(): bool
    {
        return static::blogImages()->enabled();
    }

    private static function blogImages(): ImageResolver
    {
        return ImageResolver::make(config('blog.image_resolver'));
    }
}
