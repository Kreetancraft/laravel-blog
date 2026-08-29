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

class EditTag extends Component
{
    use InteractsWithTagForm;
    use ValidatesInline;

    public Tag $tag;

    public function mount(Tag $tag): void
    {
        $this->authorize('update', $tag);

        $this->tag = $tag;
        $this->name = $tag->name;

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
        $this->authorize('update', $this->tag);

        $this->validate();

        $this->tag->update($this->tagData());

        $this->resetInlineValidation();

        Flux::toast(variant: 'success', text: __('Saved.'));
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->tag);

        $this->tag->delete();

        Flux::toast(variant: 'success', text: __('Deleted.'));

        $this->redirect(Routes::to('tags'), navigate: true);
    }

    #[Title('Edit Tag - Admin')]
    public function render()
    {
        return view('blog::livewire.edit-tag')->layout(Layout::admin());
    }
}
