<?php

namespace Kreetancraft\Blog\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Enums\PostStatus;
use Kreetancraft\Blog\Enums\SeriesStatus;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Comment;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Blog\Models\Tag;
use Kreetancraft\Blog\Support\BlogApiCache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BlogsRepository implements BlogsContract
{
    /** @param array<string, mixed> $filters */
    public function paginatePosts(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Post::query()
            ->published()
            ->with(['author', 'series', 'categories', 'tags'])
            ->withCount('approvedComments as approved_comments_count');

        $request = Request::create('/', 'GET', ['filter' => $filters]);

        $posts = QueryBuilder::for($query, $request)
            ->allowedFilters(
                AllowedFilter::callback('search', function ($q, $value): void {
                    $q->where(function ($inner) use ($value): void {
                        $inner->where('title', 'like', '%'.$value.'%')
                            ->orWhere('excerpt', 'like', '%'.$value.'%')
                            ->orWhere('content', 'like', '%'.$value.'%');
                    });
                }),
                AllowedFilter::callback('category', fn ($q, $value) => $q->whereHas('categories', fn ($c) => $c->where('slug', $value))),
                AllowedFilter::callback('tag', fn ($q, $value) => $q->whereHas('tags', fn ($t) => $t->where('slug', $value))),
                AllowedFilter::callback('author', fn ($q, $value) => $q->whereHas('author', fn ($a) => $a->where('slug', $value))),
                AllowedFilter::callback('series', fn ($q, $value) => $q->whereHas('series', fn ($s) => $s->where('slug', $value))),
                AllowedFilter::callback('featured', fn ($q, $value) => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? $q->where('is_featured', true) : $q),
            )
            ->allowedSorts('published_at', 'title')
            ->defaultSort('-published_at')
            ->paginate($perPage);

        // Images resolve through blog.image_resolver rather than a relation on
        // the model, so they are warmed for the whole page in one query here.
        // Without this every card costs an extra round trip.
        Post::preloadImages($posts->getCollection(), (string) config('blog.collections.featured', 'featured'));

        return $posts;
    }

    public function getPublicPost(string $slug): Post
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with([
                'author',
                'series',
                'categories',
                'tags',
                'seoMeta',
                'approvedComments' => fn ($query) => $query->whereNull('parent_id')->latest(),
                'approvedComments.user',
                'approvedComments.replies' => fn ($query) => $query->approved()->oldest(),
                'approvedComments.replies.user',
            ])
            ->withCount('approvedComments as approved_comments_count')
            ->firstOrFail();

        $post->setRelation('previousPost', $this->neighbour($post, before: true));
        $post->setRelation('nextPost', $this->neighbour($post, before: false));
        $post->setRelation('relatedPosts', $this->related($post));

        return $post;
    }

    private function neighbour(Post $post, bool $before): ?Post
    {
        $operator = $before ? '<' : '>';
        $direction = $before ? 'desc' : 'asc';

        return Post::published()
            ->where(fn ($q) => $q
                ->where('published_at', $operator, $post->published_at)
                ->orWhere(fn ($tie) => $tie
                    ->where('published_at', $post->published_at)
                    ->where('id', $operator, $post->id)))
            ->orderBy('published_at', $direction)
            ->orderBy('id', $direction)
            ->first(['id', 'slug', 'title']);
    }

    private function related(Post $post): Collection
    {
        $related = Post::published()
            ->whereKeyNot($post->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('blog_categories.id', $post->categories->pluck('id')))
            ->with(['author', 'series', 'categories', 'tags'])
            ->withCount('approvedComments as approved_comments_count')
            ->latest('published_at')
            ->limit(3)
            ->get();

        Post::preloadImages($related, (string) config('blog.collections.featured', 'featured'));

        return $related;
    }

    /** @return Collection<int, Category> */
    public function listCategories(): Collection
    {
        return Category::query()
            ->withCount(['posts as posts_count' => fn ($q) => $q->where('status', PostStatus::Published->value)->where('published_at', '<=', now())])
            ->orderBy('name')
            ->get();
    }

    public function showCategory(string $slug): Category
    {
        return Category::query()
            ->where('slug', $slug)
            ->with('seoMeta')
            ->withCount(['posts as posts_count' => fn ($q) => $q->where('status', PostStatus::Published->value)->where('published_at', '<=', now())])
            ->firstOrFail();
    }

    public function showAuthor(string $slug): Author
    {
        return Author::query()
            ->where('slug', $slug)
            ->with('seoMeta')
            ->withCount(['posts as posts_count' => fn ($q) => $q->where('status', PostStatus::Published->value)->where('published_at', '<=', now())])
            ->firstOrFail();
    }

    public function showSeries(string $slug): Series
    {
        return Series::query()
            ->where('slug', $slug)
            ->where('status', SeriesStatus::Active->value)
            ->with('seoMeta')
            ->withCount(['posts as posts_count' => fn ($q) => $q->where('status', PostStatus::Published->value)->where('published_at', '<=', now())])
            ->firstOrFail();
    }

    /** @return Collection<int, Tag> */
    public function listTags(): Collection
    {
        return Tag::query()
            ->withCount(['posts as posts_count' => fn ($q) => $q->where('status', PostStatus::Published->value)->where('published_at', '<=', now())])
            ->orderBy('name')
            ->get();
    }

    /** @param array<string, mixed> $data */
    public function createComment(Post $post, array $data): Comment
    {
        return $post->comments()->create($data);
    }

    /** @param array<string, mixed> $data */
    public function createPost(array $data): Post
    {
        return Post::create($data);
    }

    /** @param array<string, mixed> $data */
    public function updatePost(Post $post, array $data): Post
    {
        $post->update($data);

        return $post;
    }

    public function findOrFail(int $id): Post
    {
        return Post::findOrFail($id);
    }

    public function deletePost(Post $post): void
    {
        $post->delete();
    }

    /** @param array<string, mixed> $data */
    public function createAuthor(array $data): Author
    {
        return Author::create($data);
    }

    /** @param array<string, mixed> $data */
    public function updateAuthor(Author $author, array $data): Author
    {
        $author->update($data);

        return $author;
    }

    /** @param array<string, mixed> $data */
    public function createSeries(array $data): Series
    {
        return Series::create($data);
    }

    /** @param array<string, mixed> $data */
    public function updateSeries(Series $series, array $data): Series
    {
        $series->update($data);

        return $series;
    }

    /** @param array<string, mixed> $filters */
    public function paginatePostsAdmin(array $filters, string $sort, int $perPage): LengthAwarePaginator
    {
        $request = Request::create('/', 'GET', [
            'filter' => $filters,
            'sort' => $sort,
        ]);

        return QueryBuilder::for(Post::class, $request)
            ->with(['author', 'categories', 'series'])
            ->allowedFilters(
                AllowedFilter::callback('search', function ($query, $value): void {
                    $query->where(function ($inner) use ($value): void {
                        $inner->where('title', 'like', '%'.$value.'%')
                            ->orWhere('slug', 'like', '%'.$value.'%');
                    });
                }),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('author_id'),
                AllowedFilter::callback('category', function ($query, $value): void {
                    $query->whereHas('categories', fn ($inner) => $inner->where('blog_categories.id', $value));
                }),
            )
            ->allowedSorts('title', 'status', 'published_at', 'created_at')
            ->paginate($perPage);
    }

    public function publishedCount(): int
    {
        return Post::where('status', 'published')->count();
    }

    /** @return Collection<int, Category> */
    public function categoriesForSelect(): Collection
    {
        return Category::orderBy('name')->get();
    }

    /** @return Collection<int, Author> */
    public function authorsForSelect(): Collection
    {
        return Author::orderBy('name')->get();
    }

    /** @return Collection<int, Tag> */
    public function tagsForSelect(): Collection
    {
        return Tag::orderBy('name')->get();
    }

    /** @return Collection<int, Post> */
    public function searchPublished(string $query, int $limit): Collection
    {
        $like = "%{$query}%";

        $posts = Post::published()
            ->where(function ($q) use ($like): void {
                $q->where('title', 'LIKE', $like)
                    ->orWhere('excerpt', 'LIKE', $like)
                    ->orWhere('content', 'LIKE', $like);
            })
            ->limit($limit)
            ->get();

        // The eager-load this replaced constrained `collection_name` on the
        // nested media relation, where that column only ever holds the library
        // name — the value 'featured' lives on the attachment. The filter
        // matched nothing, and because the unfiltered relation was still
        // loaded the fallback never fired: every search result came back with a
        // null image, silently.
        Post::preloadImages($posts, (string) config('blog.collections.featured', 'featured'));

        return $posts;
    }

    public function publishScheduledPosts(): int
    {
        $updated = Post::query()
            ->where('status', PostStatus::Scheduled->value)
            ->where('published_at', '<=', now())
            ->update(['status' => PostStatus::Published->value]);

        if ($updated > 0) {
            BlogApiCache::flush();
        }

        return $updated;
    }
}
