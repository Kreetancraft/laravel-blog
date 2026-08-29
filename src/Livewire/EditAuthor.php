<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Actions\UpdateAuthorAction;
use Kreetancraft\Blog\Concerns\ValidatesInline;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Livewire\Concerns\InteractsWithAuthorForm;
use Kreetancraft\Blog\Models\Author;
use Livewire\Attributes\Title;
use Livewire\Component;

class EditAuthor extends Component
{
    use InteractsWithAuthorForm;
    use ValidatesInline;

    public Author $author;

    public function mount(Author $author): void
    {
        $this->authorize('update', $author);

        $this->author = $author;
        $this->name = $author->name;
        $this->bio = (string) $author->bio;
        $this->avatarMedia = $author->imageList((string) config('blog.collections.author_avatar', 'avatar'));

        $this->fillSeoForm($author);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return $this->authorRules();
    }

    public function save(): void
    {
        $this->authorize('update', $this->author);

        $this->validate();

        UpdateAuthorAction::run($this->author, $this->authorData());

        $this->author->refresh();

        $this->resetInlineValidation();

        Flux::toast(variant: 'success', text: __('Author saved.'));
    }

    #[Title('Edit Author - Admin')]
    public function render()
    {
        return view('blog::livewire.edit-author', [
            'seoAnalysis' => $this->seoAnalysis(),
        ])->layout(Layout::admin());
    }
}
