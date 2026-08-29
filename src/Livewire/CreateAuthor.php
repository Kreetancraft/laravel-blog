<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Actions\CreateAuthorAction;
use Kreetancraft\Blog\Concerns\ValidatesInline;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Livewire\Concerns\InteractsWithAuthorForm;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Routes;
use Livewire\Attributes\Title;
use Livewire\Component;

class CreateAuthor extends Component
{
    use InteractsWithAuthorForm;
    use ValidatesInline;

    public function mount(): void
    {
        $this->authorize('create', Author::class);
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
        $this->authorize('create', Author::class);

        $this->validate();

        CreateAuthorAction::run($this->authorData());

        $this->resetInlineValidation();

        Flux::toast(variant: 'success', text: __('Author created.'));

        $this->redirect(Routes::to('authors'), navigate: true);
    }

    #[Title('New Author - Admin')]
    public function render()
    {
        return view('blog::livewire.create-author', [
            'seoAnalysis' => $this->seoAnalysis(),
        ])->layout(Layout::admin());
    }
}
