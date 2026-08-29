<?php

namespace Kreetancraft\Blog\Livewire\Concerns;

use Illuminate\Validation\Rule as LaravelRule;
use Kreetancraft\Blog\Enums\SeriesStatus;
use Kreetancraft\Seo\Livewire\Concerns\InteractsWithSeoForm;
use Livewire\Attributes\On;
use SanderMuller\FluentValidation\FluentRule as Rule;

/**
 * Shared state, validation and media wiring for the series Create/Edit
 * Livewire forms.
 */
trait InteractsWithSeriesForm
{
    use InteractsWithSeoForm;

    public string $title = '';

    public string $description = '';

    public string $status = 'draft';

    /**
     * @var array<int, array{id: int, url: string, name: string}>
     */
    public array $coverMedia = [];

    protected function seoAnalysisSubject(): array
    {
        return [
            'title' => $this->title,
            'slug' => (string) ($this->slug ?? ''),
            'content' => $this->description,
            'path' => isset($this->series) ? str_replace('{slug}', $this->series->slug, config('seo.paths.blog_series')) : '/',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function seriesRules(): array
    {
        return array_merge(
            [
                'title' => Rule::string()->required()->max(255),
                'description' => Rule::string()->nullable()->max(10000),
                'status' => ['required', LaravelRule::enum(SeriesStatus::class)],
            ],
            $this->seoRules()
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function seriesData(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'status' => $this->status,
            'cover_media' => array_values(array_map(fn ($m) => (int) $m['id'], $this->coverMedia)),
            'seo' => $this->seoData(),
        ];
    }

    #[On('media-picked')]
    public function onMediaPicked(array $ids, string $group, array $items = []): void
    {
        if ($group === 'blog-series-cover') {
            $this->coverMedia = $this->normalizeMediaItems($items);

            return;
        }

        $this->onSeoOgMediaPicked($ids, $group, $items);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array{id: int, url: string, name: string}>
     */
    protected function normalizeMediaItems(array $items): array
    {
        return array_values(array_map(fn (array $i) => [
            'id' => (int) ($i['id'] ?? 0),
            'url' => (string) ($i['url'] ?? ''),
            'name' => (string) ($i['name'] ?? ''),
        ], $items));
    }
}
