<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Concerns\ValidatesInline;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Livewire\Concerns\InteractsWithTagForm;
use Kreetancraft\Blog\Models\Tag;
use Kreetancraft\Blog\Routes;
use Livewire\Attributes\Title;
use Livewire\Component;

class CreateTag extends Component
{
    use InteractsWithTagForm;
    use ValidatesInline;

    public ?Tag $tag = null;

    public function mount(): void
    {
        $this->authorize('create', Tag::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return $this->tagRules();
    }

    public function save(): void
    {
        $this->authorize('create', Tag::class);

        $this->validate();

        Tag::create($this->tagData());

        $this->resetInlineValidation();

        Flux::toast(variant: 'success', text: __('Tag created.'));

        $this->redirect(Routes::to('tags'), navigate: true);
    }

    #[Title('New Tag - Admin')]
    public function render()
    {
        return view('blog::livewire.create-tag')->layout(Layout::admin());
    }
}
