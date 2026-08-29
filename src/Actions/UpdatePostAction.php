<?php

namespace Kreetancraft\Blog\Actions;

use Illuminate\Support\Facades\DB;
use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Enums\PostStatus;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Seo\Actions\SaveSeoAction;
use Kreetancraft\Seo\Support\SaveSeoData;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePostAction
{
    use AsAction;

    public function __construct(
        private readonly BlogsContract $blogs,
    ) {}

    /**
     * Update a post's fields, categories, tags, and featured image.
     *
     * @param  array<string, mixed>  $data  May include `categories`, `tags`,
     *                                      and `featured_media` id arrays.
     */
    public function handle(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $categories = $data['categories'] ?? null;
            $tags = $data['tags'] ?? null;
            $featured = $data['featured_media'] ?? null;
            $seo = $data['seo'] ?? [];
            unset($data['categories'], $data['tags'], $data['featured_media'], $data['seo']);

            if (($data['status'] ?? null) === PostStatus::Published->value && blank($data['published_at'] ?? null)) {
                $data['published_at'] = now();
            }

            $post = $this->blogs->updatePost($post, $data);

            if ($categories !== null) {
                $post->categories()->sync($categories);
            }

            if ($tags !== null) {
                $post->tags()->sync($tags);
            }

            if ($featured !== null) {
                $post->syncAttachedMedia($featured, 'featured');
            }

            SaveSeoAction::run($post, SaveSeoData::fromArray($seo));

            return $post;
        });
    }
}
