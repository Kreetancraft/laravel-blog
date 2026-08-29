<?php

namespace Kreetancraft\Blog\Actions;

use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Models\Post;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPublicPostAction
{
    use AsAction;

    public function __construct(
        private readonly BlogsContract $blogs,
    ) {}

    public function handle(string $slug): Post
    {
        return $this->blogs->getPublicPost($slug);
    }
}
