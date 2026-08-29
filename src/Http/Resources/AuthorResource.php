<?php

namespace Kreetancraft\Blog\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Seo\Services\JsonLdBuilder;
use Kreetancraft\Seo\Support\SeoPayload;

/**
 * Author landing page: bio + SEO. Their posts come from
 * /blog/posts?filter[author]={slug}.
 *
 * @mixin Author
 */
class AuthorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $path = str_replace('{slug}', $this->slug, config('seo.paths.blog_author'));

        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'bio' => $this->bio,
            'posts_count' => (int) ($this->posts_count ?? 0),
            'seo' => SeoPayload::for($this->resource, [
                JsonLdBuilder::collectionPage($this->name, $path),
                JsonLdBuilder::breadcrumbList([
                    ['name' => __('Home'), 'path' => '/'],
                    ['name' => __('Blog'), 'path' => '/blog'],
                    ['name' => $this->name, 'path' => $path],
                ]),
            ]),
        ];
    }
}
