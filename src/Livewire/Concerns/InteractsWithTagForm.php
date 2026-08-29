<?php

namespace Kreetancraft\Blog\Livewire\Concerns;

use Illuminate\Validation\Rule as LaravelRule;

/**
 * Shared state and validation for the tag Create/Edit forms.
 *
 * Deliberately no SEO panel. A tag is a light cross-cutting label, not a
 * landing page: Tag is the one taxonomy that does not use HasSeo, and the
 * sitemap provider does not list tag URLs. Giving it a meta form here would
 * write rows nothing reads.
 */
trait InteractsWithTagForm
{
    public string $name = '';

    /**
     * @return array<string, mixed>
     */
    protected function tagRules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                LaravelRule::unique('blog_tags', 'name')->ignore($this->tag?->id),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function tagData(): array
    {
        return ['name' => $this->name];
    }
}
