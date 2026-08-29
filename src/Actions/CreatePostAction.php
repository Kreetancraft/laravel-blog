<?php

namespace Kreetancraft\Blog\Actions;

use Illuminate\Support\Facades\DB;
use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Enums\PostStatus;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Seo\Actions\SaveSeoAction;
use Kreetancraft\Seo\Support\SaveSeoData;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePostAction
{
    use AsAction;

    public function __construct(
        private readonly BlogsContract $blogs,
    ) {}

    /**
     * Create a post with its categories, tags, and featured image.
     *
     * @param  array<string, mixed>  $data  May include `categories`, `tags`,
     *                                      and `featured_media` id arrays.
     */
    public function handle(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            $categories = $data['categories'] ?? [];
            $tags = $data['tags'] ?? [];
            $featured = $data['featured_media'] ?? [];
            $seo = $data['seo'] ?? [];
            unset($data['categories'], $data['tags'], $data['featured_media'], $data['seo']);

            if (($data['status'] ?? null) === PostStatus::Published->value && blank($data['published_at'] ?? null)) {
                $data['published_at'] = now();
            }

            $post = $this->blogs->createPost($data);

            $post->categories()->sync($categories);
            $post->tags()->sync($tags);
            $post->syncAttachedMedia($featured, 'featured');

            SaveSeoAction::run($post, SaveSeoData::fromArray($seo));

            return $post;
        });
    }
}
