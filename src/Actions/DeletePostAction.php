<?php

namespace Kreetancraft\Blog\Actions;

use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Models\Post;
use Lorisleiva\Actions\Concerns\AsAction;

class DeletePostAction
{
    use AsAction;

    public function __construct(
        private readonly BlogsContract $blogs,
    ) {}

    /**
     * Soft-delete a post. Its comments and pivot rows stay in place until
     * the post is force-deleted (FKs cascade at that point).
     */
    public function handle(Post $post): void
    {
        $this->blogs->deletePost($post);
    }
}
