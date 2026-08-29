<?php

namespace Kreetancraft\Blog\Http\Resources;

use Illuminate\Http\Request;
use Kreetancraft\Blog\Seo\BlogJsonLd;
use Kreetancraft\Seo\Services\JsonLdBuilder;
use Kreetancraft\Seo\Support\SeoPayload;

/**
 * Full representation of a post for the public detail page: adds the HTML
 * content, the SEO block, and the approved comment tree.
 */
class PostDetailResource extends PostResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'content' => $this->content,
            'author_bio' => $this->whenLoaded('author', fn (): ?string => $this->author?->bio),
            'previous_post' => $this->whenLoaded('previousPost', fn (): ?array => $this->previousPost
                ? ['slug' => $this->previousPost->slug, 'title' => $this->previousPost->title]
                : null),
            'next_post' => $this->whenLoaded('nextPost', fn (): ?array => $this->nextPost
                ? ['slug' => $this->nextPost->slug, 'title' => $this->nextPost->title]
                : null),
            'related_posts' => PostResource::collection($this->whenLoaded('relatedPosts')),
            'seo' => SeoPayload::for($this->resource, [
                BlogJsonLd::posting($this->resource),
                JsonLdBuilder::breadcrumbList([
                    ['name' => __('Home'), 'path' => '/'],
                    ['name' => __('Blog'), 'path' => '/blog'],
                    ['name' => $this->title, 'path' => str_replace('{slug}', $this->slug, config('seo.paths.blog_post'))],
                ]),
            ]),
            'comments' => CommentResource::collection($this->whenLoaded('approvedComments')),
        ]);
    }
}
