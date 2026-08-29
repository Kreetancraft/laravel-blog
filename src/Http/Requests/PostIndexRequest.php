<?php

namespace Kreetancraft\Blog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Kreetancraft\Blog\Http\Requests\Concerns\PaginatesApi;
use SanderMuller\FluentValidation\FluentRule as Rule;

class PostIndexRequest extends FormRequest
{
    use PaginatesApi;

    /**
     * The public listing endpoint is open.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $sorts = implode(',', self::ALLOWED_SORTS);

        return [
            'filter' => Rule::array()->nullable(),
            'filter.search' => Rule::string()->nullable()->max(255),
            'filter.category' => Rule::string()->nullable()->exists('blog_categories', 'slug'),
            'filter.tag' => Rule::string()->nullable()->exists('blog_tags', 'slug'),
            'filter.author' => Rule::string()->nullable()->exists('blog_authors', 'slug'),
            'filter.series' => Rule::string()->nullable()->exists('blog_series', 'slug'),
            // Query-string boolean: accept the ergonomic ?filter[featured]=true form.
            'filter.featured' => Rule::string()->nullable()->rule('in:true,false,1,0'),
            'sort' => Rule::string()->nullable()->rule("in:{$sorts}"),
            ...$this->paginationRules(),
        ];
    }

    /**
     * Sort keys accepted by the listing endpoint (ascending and descending).
     *
     * @var list<string>
     */
    public const ALLOWED_SORTS = [
        'published_at', '-published_at',
        'title', '-title',
    ];
}
