<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Models\Category;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The category listing.
 *
 * Creating and editing happen on their own pages: a category carries the full
 * SEO panel — meta, Open Graph, Twitter and three live previews — and that does
 * not belong in a modal. Deleting stays here, where the row is.
 */
class ManageCategories extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'name')]
    public string $sort = 'name';

    public function mount(): void
    {
        $this->authorize('viewAny', Category::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $this->sort = $this->sort === $field ? '-'.$field : $field;
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->authorize('delete', $category);
        $category->delete();

        Flux::toast(variant: 'success', text: __('Deleted.'));
    }

    #[Title('Categories - Admin')]
    public function render()
    {
        $query = Category::query()->withCount('posts');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('slug', 'like', '%'.$this->search.'%');
            });
        }

        $sortField = ltrim($this->sort, '-');
        $direction = str_starts_with($this->sort, '-') ? 'desc' : 'asc';

        if (in_array($sortField, ['name', 'slug', 'posts_count'])) {
            $query->orderBy($sortField, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        return view('blog::livewire.manage-categories', [
            'categories' => $query->paginate(15),
        ])->layout(Layout::admin());
    }
}
