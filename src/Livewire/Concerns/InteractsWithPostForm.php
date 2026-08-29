<?php

namespace Kreetancraft\Blog\Livewire\Concerns;

use Illuminate\Validation\Rule as LaravelRule;
use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Enums\PostStatus;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Seo\Livewire\Concerns\InteractsWithSeoForm;
use Livewire\Attributes\On;
use SanderMuller\FluentValidation\FluentRule as Rule;

/**
 * Shared state, validation and media wiring for the post Create/Edit
 * Livewire forms.
 */
trait InteractsWithPostForm
{
    use InteractsWithSeoForm;

    private ?BlogsContract $blogRepo = null;

    protected function blogs(): BlogsContract
    {
        return $this->blogRepo ??= app(BlogsContract::class);
    }

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $content = '';

    public string $status = 'draft';

    public ?string $published_at = null;

    public $author_id = null;

    public $series_id = null;

    public ?int $order_in_series = null;

    public bool $is_featured = false;

    /**
     * @var array<int, int>
     */
    public array $categories = [];

    /**
     * @var array<int, int>
     */
    public array $tags = [];

    /**
     * @var array<int, array{id: int, url: string, name: string}>
     */
    public array $featuredMedia = [];

    protected function seoAnalysisSubject(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'path' => str_replace('{slug}', $this->slug, config('seo.paths.blog_post')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function postRules(?int $ignorePostId = null): array
    {
        $publishedAtRules = ['nullable', 'date'];

        if ($this->status === PostStatus::Scheduled->value) {
            $publishedAtRules = ['required', 'date', 'after:now'];
        }

        return array_merge(
            [
                'title' => Rule::string()->required()->max(255),
                'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', LaravelRule::unique('blog_posts', 'slug')->ignore($ignorePostId)],
                'excerpt' => Rule::string()->nullable()->max(1000),
                'content' => Rule::string()->nullable()->max(100000),
                'status' => ['required', LaravelRule::enum(PostStatus::class)],
                'published_at' => $publishedAtRules,
                'author_id' => Rule::numeric()->required()->exists('blog_authors', 'id'),
                'series_id' => Rule::numeric()->nullable()->exists('blog_series', 'id'),
                'order_in_series' => Rule::numeric()->nullable()->min(0),
                'is_featured' => ['boolean'],
                'categories' => ['array'],
                'categories.*' => ['integer', 'exists:blog_categories,id'],
                'tags' => ['array'],
                'tags.*' => ['integer', 'exists:blog_tags,id'],
            ],
            $this->seoRules()
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function postData(): array
    {
        $data = [
            'title' => $this->title,
            'excerpt' => $this->excerpt ?: null,
            'content' => $this->content ?: null,
            'status' => $this->status,
            'published_at' => $this->published_at ?: null,
            'author_id' => $this->author_id,
            'series_id' => $this->series_id ?: null,
            'order_in_series' => $this->series_id ? $this->order_in_series : null,
            'is_featured' => $this->is_featured,
            'categories' => array_map('intval', $this->categories),
            'tags' => array_map('intval', $this->tags),
            'featured_media' => array_values(array_map(fn ($m) => (int) $m['id'], $this->featuredMedia)),
            'seo' => $this->seoData(),
        ];

        if ($this->slug !== '') {
            $data['slug'] = $this->slug;
        }

        return $data;
    }

    /**
     * Publishing (or scheduling) requires the publish-blogs permission.
     */
    protected function guardPublishing(): void
    {
        if (in_array($this->status, [PostStatus::Published->value, PostStatus::Scheduled->value], true)) {
            $this->authorize('publish', Post::class);
        }
    }

    #[On('media-picked')]
    public function onMediaPicked(array $ids, string $group, array $items = []): void
    {
        if ($group === 'blog-post-featured') {
            $this->featuredMedia = $this->normalizeMediaItems($items);

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

    /**
     * @return array<string, mixed>
     */
    protected function postFormOptions(): array
    {
        return [
            'authors' => $this->blogs()->authorsForSelect(),
            'seriesOptions' => Series::orderBy('title')->get(),
            'categoryOptions' => $this->blogs()->categoriesForSelect(),
            'tagOptions' => $this->blogs()->tagsForSelect(),
        ];
    }
}
