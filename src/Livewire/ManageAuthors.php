<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Illuminate\Database\QueryException;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Models\Author;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class ManageAuthors extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sort = 'name';

    /**
     * @var array<string, array<string, string>>
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'name'],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Author::class);
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
        $author = Author::findOrFail($id);
        $this->authorize('delete', $author);

        try {
            $author->delete();
            Flux::toast(variant: 'success', text: __('Author deleted.'));
        } catch (QueryException) {
            Flux::toast(variant: 'danger', text: __('Cannot delete — the author still has posts.'));
        }
    }

    #[Title('Authors - Admin')]
    public function render()
    {
        $query = Author::query()->withCount('posts');

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

        return view('blog::livewire.manage-authors', [
            'authors' => $query->paginate(15),
        ])->layout(Layout::admin());
    }
}
