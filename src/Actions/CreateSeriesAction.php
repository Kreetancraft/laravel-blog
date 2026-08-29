<?php

namespace Kreetancraft\Blog\Actions;

use Illuminate\Support\Facades\DB;
use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Seo\Actions\SaveSeoAction;
use Kreetancraft\Seo\Support\SaveSeoData;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateSeriesAction
{
    use AsAction;

    public function __construct(
        private readonly BlogsContract $blogs,
    ) {}

    /**
     * Create a series with an optional cover image.
     *
     * @param  array<string, mixed>  $data  May include a `cover_media` id array.
     */
    public function handle(array $data): Series
    {
        return DB::transaction(function () use ($data) {
            $cover = $data['cover_media'] ?? [];
            unset($data['cover_media']);

            $seo = $data['seo'] ?? [];
            unset($data['seo']);

            $series = $this->blogs->createSeries($data);

            $series->syncAttachedMedia($cover, 'cover');

            SaveSeoAction::run($series, SaveSeoData::fromArray($seo));

            return $series;
        });
    }
}
