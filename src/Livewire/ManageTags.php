<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Illuminate\Validation\Rule as LaravelRule;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Models\Tag;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lookup CRUD for blog tags — name-only records with auto-slugs (HasSlug).
 */
class ManageTags extends Component
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

    public ?int $editingId = null;

    public string $name = '';

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

    public function openCreate(): void
    {
        $this->authorize('create', Tag::class);
        $this->resetForm();
        Flux::modal('tag-form')->show();
    }

    public function openEdit(int $id): void
    {
        $this->resetForm();

        $tag = Tag::findOrFail($id);
        $this->authorize('update', $tag);
        $this->editingId = $tag->id;
        $this->name = $tag->name;

        Flux::modal('tag-form')->show();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', LaravelRule::unique('blog_tags', 'name')->ignore($this->editingId)],
        ];
    }

    public function save(): void
    {
        if ($this->editingId !== null) {
            $this->authorize('update', Tag::findOrFail($this->editingId));
        } else {
            $this->authorize('create', Tag::class);
        }
        $this->validate();

        if ($this->editingId !== null) {
            Tag::findOrFail($this->editingId)->update(['name' => $this->name]);
        } else {
            Tag::create(['name' => $this->name]);
        }

        Flux::modal('tag-form')->close();
        $this->resetForm();
        Flux::toast(variant: 'success', text: __('Saved.'));
    }

    public function delete(int $id): void
    {
        $tag = Tag::findOrFail($id);
        $this->authorize('delete', $tag);
        $tag->delete();

        Flux::toast(variant: 'success', text: __('Deleted.'));
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name']);
        $this->resetValidation();
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
