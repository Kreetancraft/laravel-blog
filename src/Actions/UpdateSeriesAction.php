<?php

namespace Kreetancraft\Blog\Actions;

use Illuminate\Support\Facades\DB;
use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Seo\Actions\SaveSeoAction;
use Kreetancraft\Seo\Support\SaveSeoData;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateSeriesAction
{
    use AsAction;

    public function __construct(
        private readonly BlogsContract $blogs,
    ) {}

    /**
     * Update a series' fields and cover image.
     *
     * @param  array<string, mixed>  $data  May include a `cover_media` id array.
     */
    public function handle(Series $series, array $data): Series
    {
        return DB::transaction(function () use ($series, $data) {
            $cover = $data['cover_media'] ?? null;
            unset($data['cover_media']);

            $seo = $data['seo'] ?? [];
            unset($data['seo']);

            $series = $this->blogs->updateSeries($series, $data);

            if ($cover !== null) {
                $series->syncAttachedMedia($cover, 'cover');
            }

            SaveSeoAction::run($series, SaveSeoData::fromArray($seo));

            return $series;
        });
    }
}
