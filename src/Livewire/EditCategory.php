<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Kreetancraft\Blog\Concerns\ValidatesInline;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Livewire\Concerns\InteractsWithCategoryForm;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Routes;
use Kreetancraft\Seo\Actions\SaveSeoAction;
use Kreetancraft\Seo\Support\SaveSeoData;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

class EditCategory extends Component
{
    use InteractsWithCategoryForm;
    use ValidatesInline;

    public Category $category;

    public function mount(Category $category): void
    {
        $this->authorize('update', $category);

        $this->category = $category;
        $this->name = $category->name;
        $this->description = (string) $category->description;

        $this->fillSeoForm($category);
    }

    #[On('media-picked')]
    public function onMediaPicked(array $ids, string $group, array $items = []): void
    {
        $this->onCategoryMediaPicked($ids, $group, $items);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return $this->categoryRules();
    }

    public function save(): void
    {
        $this->authorize('update', $this->category);

        $this->validate();

        DB::transaction(function (): void {
            $this->category->update($this->categoryData());

            SaveSeoAction::run($this->category, SaveSeoData::fromArray($this->seoData()));
        });

        $this->resetInlineValidation();

        Flux::toast(variant: 'success', text: __('Saved.'));
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->category);

        $this->category->delete();

        Flux::toast(variant: 'success', text: __('Deleted.'));

        $this->redirect(Routes::to('categories'), navigate: true);
    }

    #[Title('Edit Category - Admin')]
    public function render()
    {
        return view('blog::livewire.edit-category', [
            'seoAnalysis' => $this->seoAnalysis(),
        ])->layout(Layout::admin());
    }
}
