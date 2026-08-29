<?php

namespace Kreetancraft\Blog\Http\Resources;

use Illuminate\Http\Request;
use Kreetancraft\Seo\Services\JsonLdBuilder;
use Kreetancraft\Seo\Support\SeoPayload;

/**
 * Category landing page: the list payload plus SEO. Its posts come from
 * /blog/posts?filter[category]={slug}.
 */
class CategoryDetailResource extends CategoryResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $path = str_replace('{slug}', $this->slug, config('seo.paths.blog_category'));

        return array_merge(parent::toArray($request), [
            'seo' => SeoPayload::for($this->resource, [
                JsonLdBuilder::collectionPage($this->name, $path),
                JsonLdBuilder::breadcrumbList([
                    ['name' => __('Home'), 'path' => '/'],
                    ['name' => __('Blog'), 'path' => '/blog'],
                    ['name' => $this->name, 'path' => $path],
                ]),
            ]),
        ]);
    }
}
