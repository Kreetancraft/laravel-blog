<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Actions\DeletePostAction;
use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Models\Post;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class ManagePosts extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $categoryFilter = '';

    public string $authorFilter = '';

    public string $sort = '-created_at';

    public ?int $pendingDeleteId = null;

    private BlogsContract $blogs;

    /**
     * @var array<string, array<string, string>>
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'authorFilter' => ['except' => ''],
        'sort' => ['except' => '-created_at'],
    ];

    public function boot(BlogsContract $blogs): void
    {
        $this->blogs = $blogs;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', Post::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingAuthorFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $this->sort = $this->sort === $field ? '-'.$field : $field;
        $this->resetPage();
    }

    public function confirmDelete(int $postId): void
    {
        $this->pendingDeleteId = $postId;
        Flux::modal('confirm-delete-post')->show();
    }

    public function delete(): void
    {
        if ($this->pendingDeleteId === null) {
            return;
        }

        $post = $this->blogs->findOrFail($this->pendingDeleteId);

        $this->authorize('delete', $post);

        DeletePostAction::run($post);

        $this->pendingDeleteId = null;
        Flux::modal('confirm-delete-post')->close();
        Flux::toast(variant: 'success', text: __('Post deleted.'));
    }

    #[Title('Posts - Admin')]
    public function render()
    {
        $posts = $this->blogs->paginatePostsAdmin(
            array_filter([
                'search' => $this->search,
                'status' => $this->statusFilter,
                'category' => $this->categoryFilter,
                'author_id' => $this->authorFilter,
            ], fn ($value): bool => $value !== ''),
            $this->sort,
            15,
        );

        $publishedCount = $this->blogs->publishedCount();

        return view('blog::livewire.manage-posts', [
            'posts' => $posts,
            'categoryOptions' => $this->blogs->categoriesForSelect(),
            'authorOptions' => $this->blogs->authorsForSelect(),
            'publishedCount' => $publishedCount,
        ])->layout(Layout::admin());
    }
}
