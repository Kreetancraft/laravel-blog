<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Models\Series;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class ManageSeries extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sort = 'title';

    /**
     * @var array<string, array<string, string>>
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'title'],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Series::class);
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
        $series = Series::findOrFail($id);
        $this->authorize('delete', $series);

        // Posts keep existing — the FK nulls their series_id.
        $series->delete();

        Flux::toast(variant: 'success', text: __('Series deleted.'));
    }

    #[Title('Series - Admin')]
    public function render()
    {
        $query = Series::query()->withCount('posts');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('slug', 'like', '%'.$this->search.'%');
            });
        }

        $sortField = ltrim($this->sort, '-');
        $direction = str_starts_with($this->sort, '-') ? 'desc' : 'asc';

        if (in_array($sortField, ['title', 'slug', 'status', 'posts_count'])) {
            $query->orderBy($sortField, $direction);
        } else {
            $query->orderBy('title', 'asc');
        }

        return view('blog::livewire.manage-series', [
            'seriesList' => $query->paginate(15),
        ])->layout(Layout::admin());
    }
}
