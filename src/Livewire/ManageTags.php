<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Models\Tag;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lookup CRUD for blog tags — name-only records with auto-slugs (HasSlug).
 */
class ManageTags extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'name')]
    public string $sort = 'name';

    public function mount(): void
    {
        $this->authorize('viewAny', Tag::class);
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
        $tag = Tag::findOrFail($id);
        $this->authorize('delete', $tag);
        $tag->delete();

        Flux::toast(variant: 'success', text: __('Deleted.'));
    }

    #[Title('Tags - Admin')]
    public function render()
    {
        $query = Tag::query()->withCount('posts');

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

        return view('blog::livewire.manage-tags', [
            'tags' => $query->paginate(15),
        ])->layout(Layout::admin());
    }
}
