<?php

namespace Kreetancraft\Blog\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Kreetancraft\Blog\Models\Comment;

/**
 * Public representation of an approved comment. Never exposes the
 * commenter's email address.
 *
 * @mixin Comment
 */
class CommentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author_name' => $this->displayName(),
            'author_url' => $this->author_url,
            'content' => $this->content,
            'created_at' => $this->created_at?->toIso8601String(),
            'replies' => self::collection($this->whenLoaded('replies')),
        ];
    }
}
