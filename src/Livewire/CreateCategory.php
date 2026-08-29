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

class CreateCategory extends Component
{
    use InteractsWithCategoryForm;
    use ValidatesInline;

    public ?Category $category = null;

    public function mount(): void
    {
        $this->authorize('create', Category::class);
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
        $this->authorize('create', Category::class);

        $this->validate();

        // One transaction: a category saved without its meta would look fine and
        // be missing everything the SEO screen reports on.
        DB::transaction(function (): void {
            $category = Category::create($this->categoryData());

            SaveSeoAction::run($category, SaveSeoData::fromArray($this->seoData()));
        });

        $this->resetInlineValidation();

        Flux::toast(variant: 'success', text: __('Category created.'));

        $this->redirect(Routes::to('categories'), navigate: true);
    }

    #[Title('New Category - Admin')]
    public function render()
    {
        return view('blog::livewire.create-category', [
            'seoAnalysis' => $this->seoAnalysis(),
        ])->layout(Layout::admin());
    }
}
