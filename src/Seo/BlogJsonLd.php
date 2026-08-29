<?php

namespace Kreetancraft\Blog\Seo;

use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Seo\Services\JsonLdBuilder;

/**
 * schema.org payloads for blog content.
 *
 * These live here rather than in the SEO package because they are about *this*
 * package's models. Keeping them there is what made JsonLdBuilder type-hint
 * Post and Trip, so a package that should have been a leaf imported two others.
 *
 * The site-wide pieces — the publisher node and absolute URLs — still come from
 * the SEO package, so a site speaks with one voice.
 */
class BlogJsonLd
{
    /**
     * @return array<string, mixed>
     */
    public static function posting(Post $post): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->effectiveMetaTitle(),
            'description' => $post->effectiveMetaDescription(),
            'image' => $post->ogImageUrl(),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => $post->author ? [
                '@type' => 'Person',
                'name' => $post->author->name,
                'url' => JsonLdBuilder::url(str_replace('{slug}', $post->author->slug, (string) config('seo.paths.blog_author', '/blog/author/{slug}'))),
            ] : null,
            // relationLoaded() rather than a plain check: touching an unloaded
            // relation here would fire a query per post on a listing, and under
            // preventLazyLoading it would throw instead.
            'articleSection' => $post->relationLoaded('categories') && $post->categories->isNotEmpty()
                ? $post->categories->first()->name
                : null,
            'keywords' => $post->relationLoaded('tags') && $post->tags->isNotEmpty()
                ? $post->tags->pluck('name')->implode(', ')
                : null,
            'publisher' => JsonLdBuilder::organization(),
            'mainEntityOfPage' => JsonLdBuilder::url(str_replace('{slug}', $post->slug, (string) config('seo.paths.blog_post', '/blog/{slug}'))),
        ]);
    }
}
