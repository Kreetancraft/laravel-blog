<?php

namespace Kreetancraft\Blog\Seo;

use Kreetancraft\Blog\Enums\SeriesStatus;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Seo\Contracts\ProvidesSitemapUrls;
use Kreetancraft\Seo\Support\SitemapUrl;

class BlogSitemapProvider implements ProvidesSitemapUrls
{
    /**
     * Get sitemap URLs for this provider.
     *
     * @return array<int, SitemapUrl>
     */
    public function getSitemapUrls(): array
    {
        $urls = [];

        // Posts
        foreach (Post::published()->with('seoMeta')->get() as $post) {
            if ($post->seoMeta?->noindex) {
                continue;
            }
            $urls[] = new SitemapUrl(
                path: $post->seoMeta?->canonical_url ?: str_replace('{slug}', $post->slug, config('seo.paths.blog_post')),
                type: 'Post',
                lastmod: $post->updated_at?->toAtomString()
            );
        }

        // Categories
        foreach (Category::with('seoMeta')->get() as $category) {
            if ($category->seoMeta?->noindex) {
                continue;
            }
            $urls[] = new SitemapUrl(
                path: $category->seoMeta?->canonical_url ?: str_replace('{slug}', $category->slug, config('seo.paths.blog_category')),
                type: 'Category',
                lastmod: $category->updated_at?->toAtomString()
            );
        }

        // Authors
        foreach (Author::with('seoMeta')->get() as $author) {
            if ($author->seoMeta?->noindex) {
                continue;
            }
            $urls[] = new SitemapUrl(
                path: $author->seoMeta?->canonical_url ?: str_replace('{slug}', $author->slug, config('seo.paths.blog_author')),
                type: 'Author',
                lastmod: $author->updated_at?->toAtomString()
            );
        }

        // Series
        foreach (Series::where('status', SeriesStatus::Active->value)->with('seoMeta')->get() as $series) {
            if ($series->seoMeta?->noindex) {
                continue;
            }
            $urls[] = new SitemapUrl(
                path: $series->seoMeta?->canonical_url ?: str_replace('{slug}', $series->slug, config('seo.paths.blog_series')),
                type: 'Series',
                lastmod: $series->updated_at?->toAtomString()
            );
        }

        return $urls;
    }
}
