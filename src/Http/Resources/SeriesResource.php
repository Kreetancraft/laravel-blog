<?php

namespace Kreetancraft\Blog\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Seo\Services\JsonLdBuilder;
use Kreetancraft\Seo\Support\SeoPayload;

/**
 * Series landing page: description + SEO. Its posts come from
 * /blog/posts?filter[series]={slug}.
 *
 * @mixin Series
 */
class SeriesResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $path = str_replace('{slug}', $this->slug, config('seo.paths.blog_series'));

        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'posts_count' => (int) ($this->posts_count ?? 0),
            'seo' => SeoPayload::for($this->resource, [
                JsonLdBuilder::collectionPage($this->title, $path),
                JsonLdBuilder::breadcrumbList([
                    ['name' => __('Home'), 'path' => '/'],
                    ['name' => __('Blog'), 'path' => '/blog'],
                    ['name' => $this->title, 'path' => $path],
                ]),
            ]),
        ];
    }
}
