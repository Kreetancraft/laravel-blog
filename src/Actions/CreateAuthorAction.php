<?php

namespace Kreetancraft\Blog\Actions;

use Illuminate\Support\Facades\DB;
use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Seo\Actions\SaveSeoAction;
use Kreetancraft\Seo\Support\SaveSeoData;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAuthorAction
{
    use AsAction;

    public function __construct(
        private readonly BlogsContract $blogs,
    ) {}

    /**
     * Create an author with an optional avatar image.
     *
     * @param  array<string, mixed>  $data  May include an `avatar_media` id array.
     */
    public function handle(array $data): Author
    {
        return DB::transaction(function () use ($data) {
            $avatar = $data['avatar_media'] ?? [];
            unset($data['avatar_media']);

            $seo = $data['seo'] ?? [];
            unset($data['seo']);

            $author = $this->blogs->createAuthor($data);

            $author->syncAttachedMedia($avatar, 'avatar');

            SaveSeoAction::run($author, SaveSeoData::fromArray($seo));

            return $author;
        });
    }
}
