<?php

namespace Kreetancraft\Blog\Livewire\Concerns;

use Illuminate\Validation\Rule as LaravelRule;
use Kreetancraft\Seo\Livewire\Concerns\InteractsWithSeoForm;
use Livewire\Attributes\On;

/**
 * Shared state, validation and media wiring for the category Create/Edit forms.
 *
 * These pages replaced the modal the index screen used to open: a category
 * carries the full SEO panel — meta, Open Graph, Twitter and three live
 * previews — which does not belong in a modal.
 */
trait InteractsWithCategoryForm
{
    use InteractsWithSeoForm;

    public string $name = '';

    public string $description = '';

    /**
     * NOT an #[On] listener on the concern: Livewire allows one listener per
     * event name per component, and the host defines its own. It forwards here.
     */
    public function onCategoryMediaPicked(array $ids, string $group, array $items = []): void
    {
        $this->onSeoOgMediaPicked($ids, $group, $items);
    }

    protected function seoAnalysisSubject(): array
    {
        return [
            'title' => $this->name,
            'slug' => (string) ($this->category?->slug ?? ''),
            'content' => $this->description,
            'path' => isset($this->category)
                ? str_replace('{slug}', $this->category->slug, (string) config('seo.paths.blog_category', '/blog/category/{slug}'))
                : '/',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function categoryRules(): array
    {
        return array_merge(
            [
                'name' => [
                    'required', 'string', 'max:255',
                    LaravelRule::unique('blog_categories', 'name')->ignore($this->category?->id)->whereNull('deleted_at'),
                ],
                'description' => ['nullable', 'string', 'max:2000'],
            ],
            $this->seoRules()
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function categoryData(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description ?: null,
        ];
    }
}
