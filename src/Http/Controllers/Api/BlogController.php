<?php

namespace Kreetancraft\Blog\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Enums\CommentStatus;
use Kreetancraft\Blog\Http\Requests\PostIndexRequest;
use Kreetancraft\Blog\Http\Requests\StoreCommentRequest;
use Kreetancraft\Blog\Http\Resources\AuthorResource;
use Kreetancraft\Blog\Http\Resources\CategoryDetailResource;
use Kreetancraft\Blog\Http\Resources\CategoryResource;
use Kreetancraft\Blog\Http\Resources\PostDetailResource;
use Kreetancraft\Blog\Http\Resources\PostResource;
use Kreetancraft\Blog\Http\Resources\SeriesResource;
use Kreetancraft\Blog\Http\Resources\TagResource;
use Kreetancraft\Blog\Support\BlogApiCache;

class BlogController extends Controller
{
    public function __construct(private readonly BlogsContract $blogs) {}

    /**
     * GET /api/v1/blog/posts — paginated list of published posts.
     *
     * @response \Illuminate\Http\Resources\Json\AnonymousResourceCollection<\Illuminate\Pagination\LengthAwarePaginator<int, \Kreetancraft\Blog\Http\Resources\PostResource>>
     */
    public function index(PostIndexRequest $request): JsonResponse
    {
        $perPage = $request->perPage();
        $cacheKey = BlogApiCache::key('posts:index:'.md5($request->fullUrl()));

        return $this->cached($cacheKey, function () use ($perPage, $request) {
            $posts = $this->blogs->paginatePosts($request->validated('filter', []), $perPage)
                ->appends($request->query());

            return PostResource::collection($posts)->response()->getData(true);
        });
    }

    /**
     * GET /api/v1/blog/posts/{slug} — full detail for one published post.
     *
     * @response \Kreetancraft\Blog\Http\Resources\PostDetailResource
     */
    public function show(string $slug): JsonResponse
    {
        return $this->cached(BlogApiCache::key('posts:show:'.$slug), fn () => PostDetailResource::make($this->blogs->getPublicPost($slug))->response()->getData(true));
    }

    /**
     * GET /api/v1/blog/categories — all categories with published-post counts.
     *
     * @response \Illuminate\Http\Resources\Json\AnonymousResourceCollection<\Kreetancraft\Blog\Http\Resources\CategoryResource>
     */
    public function categories(): JsonResponse
    {
        return $this->cached(BlogApiCache::key('categories'), function () {
            return CategoryResource::collection($this->blogs->listCategories())->response()->getData(true);
        });
    }

    /**
     * GET /api/v1/blog/tags — all tags with published-post counts.
     *
     * @response \Illuminate\Http\Resources\Json\AnonymousResourceCollection<\Kreetancraft\Blog\Http\Resources\TagResource>
     */
    public function tags(): JsonResponse
    {
        return $this->cached(BlogApiCache::key('tags'), function () {
            return TagResource::collection($this->blogs->listTags())->response()->getData(true);
        });
    }

    /**
     * GET /api/v1/blog/categories/{slug} — category landing page with SEO.
     *
     * @response \Kreetancraft\Blog\Http\Resources\CategoryDetailResource
     */
    public function showCategory(string $slug): JsonResponse
    {
        return $this->cached(BlogApiCache::key('categories:show:'.$slug), function () use ($slug) {
            return (new CategoryDetailResource($this->blogs->showCategory($slug)))->response()->getData(true);
        });
    }

    /**
     * GET /api/v1/blog/authors/{slug} — author landing page with SEO.
     *
     * @response \Kreetancraft\Blog\Http\Resources\AuthorResource
     */
    public function showAuthor(string $slug): JsonResponse
    {
        return $this->cached(BlogApiCache::key('authors:show:'.$slug), function () use ($slug) {
            return (new AuthorResource($this->blogs->showAuthor($slug)))->response()->getData(true);
        });
    }

    /**
     * GET /api/v1/blog/series/{slug} — series landing page with SEO.
     *
     * @response \Kreetancraft\Blog\Http\Resources\SeriesResource
     */
    public function showSeries(string $slug): JsonResponse
    {
        return $this->cached(BlogApiCache::key('series:show:'.$slug), function () use ($slug) {
            return (new SeriesResource($this->blogs->showSeries($slug)))->response()->getData(true);
        });
    }

    /**
     * Serve a JSON payload from cache (or build and store it).
     *
     * @param  callable(): array<string, mixed>  $build
     */
    private function cached(string $key, callable $build): JsonResponse
    {
        return response()->json(Cache::remember($key, BlogApiCache::ttl(), $build));
    }

    /**
     * POST /api/v1/blog/posts/{slug}/comments — submit a comment for
     * moderation. Always created as pending; never published directly.
     */
    public function storeComment(StoreCommentRequest $request, string $slug): JsonResponse
    {
        $post = $this->blogs->getPublicPost($slug);

        $parentId = $request->validated('parent_id');

        if ($parentId !== null) {
            abort_unless(
                $post->comments()->whereKey($parentId)->exists(),
                422,
                __('The comment you are replying to does not belong to this post.'),
            );
        }

        $comment = $this->blogs->createComment($post, [
            'parent_id' => $parentId,
            'user_id' => $request->user()?->id,
            'author_name' => $request->validated('author_name'),
            'author_email' => $request->validated('author_email'),
            'author_url' => $request->validated('author_url'),
            'content' => $request->validated('content'),
            'status' => CommentStatus::Pending,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
        ]);

        return response()->json([
            'data' => [
                'id' => $comment->id,
                'status' => $comment->status->value,
            ],
        ], 201);
    }
}
