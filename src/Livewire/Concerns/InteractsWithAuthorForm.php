<?php

namespace Kreetancraft\Blog\Livewire\Concerns;

use Kreetancraft\Seo\Livewire\Concerns\InteractsWithSeoForm;
use Livewire\Attributes\On;
use SanderMuller\FluentValidation\FluentRule as Rule;

/**
 * Shared state, validation and media wiring for the author Create/Edit
 * Livewire forms.
 */
trait InteractsWithAuthorForm
{
    use InteractsWithSeoForm;

    public string $name = '';

    public string $bio = '';

    /**
     * @var array<int, array{id: int, url: string, name: string}>
     */
    public array $avatarMedia = [];

    protected function seoAnalysisSubject(): array
    {
        return [
            'title' => $this->name,
            'slug' => (string) ($this->slug ?? ''),
            'content' => $this->bio,
            'path' => isset($this->author) ? str_replace('{slug}', $this->author->slug, config('seo.paths.blog_author')) : '/',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function authorRules(): array
    {
        return array_merge(
            [
                'name' => Rule::string()->required()->max(255),
                'bio' => Rule::string()->nullable()->max(10000),
            ],
            $this->seoRules()
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function authorData(): array
    {
        return [
            'name' => $this->name,
            'bio' => $this->bio ?: null,
            'avatar_media' => array_values(array_map(fn ($m) => (int) $m['id'], $this->avatarMedia)),
            'seo' => $this->seoData(),
        ];
    }

    #[On('media-picked')]
    public function onMediaPicked(array $ids, string $group, array $items = []): void
    {
        if ($group === 'blog-author-avatar') {
            $this->avatarMedia = $this->normalizeMediaItems($items);

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
