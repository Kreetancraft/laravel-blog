<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Kreetancraft\Blog\Enums\CommentStatus;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Models\Comment;
use Kreetancraft\Blog\Models\Post;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ManageComments extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'pending';

    public string $postFilter = '';

    /**
     * @var array<int, int|string>
     */
    public array $selected = [];

    public bool $selectPage = false;

    public string $ipFilter = '';

    /**
     * @var array<string, array<string, string>>
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'pending'],
        'postFilter' => ['except' => ''],
        'ipFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Comment::class);
    }

    public function updatingSearch(): void
    {
        $this->resetListState();
    }

    public function updatingPostFilter(): void
    {
        $this->resetListState();
    }

    public function setStatusTab(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetListState();
    }

    public function updatedSelectPage(bool $value): void
    {
        $this->selected = $value
            ? $this->filteredComments()->pluck('id')->map(fn ($id) => (string) $id)->all()
            : [];
    }

    public function setStatus(int $commentId, string $status): void
    {
        $comment = Comment::findOrFail($commentId);

        $this->authorize('moderate', $comment);

        $comment->update(['status' => CommentStatus::from($status)]);

        Flux::toast(variant: 'success', text: __('Comment updated.'));
    }

    public function bulkSetStatus(string $status): void
    {
        $this->authorize('moderate', Comment::class);

        if ($this->selected === []) {
            return;
        }

        Comment::whereIn('id', $this->selected)
            ->update(['status' => CommentStatus::from($status)->value]);

        $count = count($this->selected);
        $this->selected = [];
        $this->selectPage = false;

        Flux::toast(variant: 'success', text: __(':count comment(s) updated.', ['count' => $count]));
    }

    public function delete(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);

        $this->authorize('delete', $comment);

        $comment->delete();

        Flux::toast(variant: 'success', text: __('Comment deleted.'));
    }

    public function bulkDelete(): void
    {
        $this->authorize('moderate', Comment::class);

        if ($this->selected === []) {
            return;
        }

        $count = Comment::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectPage = false;

        Flux::toast(variant: 'success', text: __(':count comment(s) deleted.', ['count' => $count]));
    }

    public function filterByIp(string $ip): void
    {
        $this->ipFilter = $ip;
        $this->resetListState();
    }

    public function clearIpFilter(): void
    {
        $this->ipFilter = '';
        $this->resetListState();
    }

    private function resetListState(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectPage = false;
    }

    /**
     * The current page of comments with all filters applied.
     *
     * @return LengthAwarePaginator<int, Comment>
     */
    private function filteredComments(): LengthAwarePaginator
    {
        $request = Request::create('/', 'GET', [
            'filter' => array_filter([
                'search' => $this->search,
                'status' => $this->statusFilter,
                'post_id' => $this->postFilter,
                'ip_address' => $this->ipFilter,
            ], fn ($value): bool => $value !== ''),
        ]);

        return QueryBuilder::for(Comment::class, $request)
            ->with(['post', 'parent', 'user'])
            ->withCount('replies')
            ->allowedFilters(
                AllowedFilter::callback('search', function ($query, $value): void {
                    $query->where(function ($inner) use ($value): void {
                        $inner->where('author_name', 'like', '%'.$value.'%')
                            ->orWhere('author_email', 'like', '%'.$value.'%')
                            ->orWhere('content', 'like', '%'.$value.'%');
                    });
                }),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('post_id'),
                AllowedFilter::exact('ip_address'),
            )
            ->latest()
            ->paginate(15);
    }

    #[Title('Comments - Admin')]
    public function render()
    {
        return view('blog::livewire.manage-comments', [
            'comments' => $this->filteredComments(),
            'statusCounts' => Comment::query()
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
            'postOptions' => Post::whereHas('comments')->orderBy('title')->get(['id', 'title']),
        ])->layout(Layout::admin());
    }
}
