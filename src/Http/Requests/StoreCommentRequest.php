<?php

namespace Kreetancraft\Blog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use SanderMuller\FluentValidation\Contracts\FluentRuleContract;
use SanderMuller\FluentValidation\FluentRule as Rule;

class StoreCommentRequest extends FormRequest
{
    /**
     * Anyone may submit a comment — it always lands in moderation.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, FluentRuleContract>
     */
    public function rules(): array
    {
        $isGuest = $this->user() === null;

        return [
            'content' => Rule::string()->required()->max(5000),
            'author_name' => $isGuest
                ? Rule::string()->required()->max(255)
                : Rule::string()->nullable()->max(255),
            'author_email' => Rule::string()
                ->when($isGuest, fn ($r) => $r->required(), fn ($r) => $r->nullable())
                ->email()
                ->max(255),
            'author_url' => Rule::string()->nullable()->url()->max(255),
            'parent_id' => Rule::integer()->nullable()->exists('blog_comments', 'id'),
            'website' => Rule::string()->prohibited(),
        ];
    }
}
