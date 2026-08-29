<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule as LaravelRule;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Seo\Actions\SaveSeoAction;
use Kreetancraft\Seo\Livewire\Concerns\InteractsWithSeoForm;
use Kreetancraft\Seo\Support\SaveSeoData;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lookup CRUD for blog categories — trivial records with auto-slugs
 * (HasSlug), written directly here rather than through an Actions layer.
 */
class ManageCategories extends Component
{
    use InteractsWithSeoForm;
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

    public string $description = '';

    #[On('media-picked')]
    public function onMediaPicked(array $ids, string $group, array $items = []): void
    {
        $this->onSeoOgMediaPicked($ids, $group, $items);
    }

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

    public function openCreate(): void
    {
        $this->authorize('create', Category::class);
        $this->resetForm();
        Flux::modal('category-form')->show();
    }

    public function openEdit(int $id): void
    {
        $this->resetForm();

        $category = Category::findOrFail($id);
        $this->authorize('update', $category);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = (string) $category->description;

        $this->fillSeoForm($category);

        Flux::modal('category-form')->show();
    }

    protected function seoAnalysisSubject(): array
    {
        return [
            'title' => $this->name,
            'slug' => (string) ($this->slug ?? ''),
            'content' => $this->description,
            'path' => isset($this->editingId) ? str_replace('{slug}', Category::find($this->editingId)?->slug ?? '', config('seo.paths.blog_category')) : '/',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return array_merge(
            [
                'name' => ['required', 'string', 'max:255', LaravelRule::unique('blog_categories', 'name')->ignore($this->editingId)->whereNull('deleted_at')],
                'description' => ['nullable', 'string', 'max:2000'],
            ],
            $this->seoRules()
        );
    }

    public function save(): void
    {
        if ($this->editingId !== null) {
            $this->authorize('update', Category::findOrFail($this->editingId));
        } else {
            $this->authorize('create', Category::class);
        }
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
        ];

        DB::transaction(function () use ($data) {
            if ($this->editingId !== null) {
                $category = Category::findOrFail($this->editingId);
                $category->update($data);
            } else {
                $category = Category::create($data);
            }

            SaveSeoAction::run($category, SaveSeoData::fromArray($this->seoData()));
        });

        Flux::modal('category-form')->close();
        $this->resetForm();
        Flux::toast(variant: 'success', text: __('Saved.'));
    }

    public function delete(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->authorize('delete', $category);
        $category->delete();

        Flux::toast(variant: 'success', text: __('Deleted.'));
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'description']);
        $this->resetSeoForm();
        $this->resetValidation();
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
