<?php

namespace Kreetancraft\Blog\Http\Requests\Concerns;

use SanderMuller\FluentValidation\FluentRule as Rule;

/**
 * The single pagination convention for every paginated API list:
 * `page` >= 1 and `per_page` 1–50 (default 12). Small bounded collections
 * (team, faqs, hero-slides, ...) intentionally return complete lists and
 * do not use this trait.
 */
trait PaginatesApi
{
    /**
     * @return array<string, mixed>
     */
    protected function paginationRules(): array
    {
        return [
            'page' => Rule::integer()->nullable()->min(1),
            'per_page' => Rule::integer()->nullable()->min(1)->max(50),
        ];
    }

    /**
     * The validated page size (clamped 1–50 by the rules above).
     */
    public function perPage(int $default = 12): int
    {
        return (int) ($this->validated('per_page') ?? $default);
    }
}
