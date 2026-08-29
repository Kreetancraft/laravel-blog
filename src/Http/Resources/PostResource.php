<?php

namespace Kreetancraft\Blog\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Kreetancraft\Blog\Models\Post;

/**
 * Summary representation of a post, used for the public listing/cards.
 *
 * @mixin Post
 */
class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'published_at' => $this->published_at?->toIso8601String(),
            'is_featured' => $this->is_featured,
            'featured_image' => $this->featuredUrl(),
            'reading_time_minutes' => max(1, (int) ceil(str_word_count(strip_tags((string) $this->content)) / 200)),
            'comment_count' => (int) ($this->approved_comments_count ?? 0),
            'author' => $this->whenLoaded('author', fn (): ?array => $this->author ? [
                'name' => $this->author->name,
                'slug' => $this->author->slug,
                'avatar' => $this->author->avatarUrl(),
            ] : null),
            'series' => $this->whenLoaded('series', fn (): ?array => $this->series ? [
                'title' => $this->series->title,
                'slug' => $this->series->slug,
                'order_in_series' => $this->order_in_series,
            ] : null),
            'categories' => $this->whenLoaded('categories', fn (): array => $this->categories->map(fn ($category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
            ])->all()),
            'tags' => $this->whenLoaded('tags', fn (): array => $this->tags->map(fn ($tag): array => [
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->all()),
        ];
    }
}
