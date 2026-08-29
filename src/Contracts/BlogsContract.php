<?php

namespace Kreetancraft\Blog\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Comment;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Blog\Models\Tag;

interface BlogsContract
{
    /** @param array<string, mixed> $filters */
    public function paginatePosts(array $filters, int $perPage): LengthAwarePaginator;

    public function getPublicPost(string $slug): Post;

    /** @return Collection<int, Category> */
    public function listCategories(): Collection;

    public function showCategory(string $slug): Category;

    public function showAuthor(string $slug): Author;

    public function showSeries(string $slug): Series;

    /** @return Collection<int, Tag> */
    public function listTags(): Collection;

    /** @param array<string, mixed> $data */
    public function createComment(Post $post, array $data): Comment;

    /** @param array<string, mixed> $data */
    public function createPost(array $data): Post;

    /** @param array<string, mixed> $data */
    public function updatePost(Post $post, array $data): Post;

    public function findOrFail(int $id): Post;

    public function deletePost(Post $post): void;

    /** @param array<string, mixed> $data */
    public function createAuthor(array $data): Author;

    /** @param array<string, mixed> $data */
    public function updateAuthor(Author $author, array $data): Author;

    /** @param array<string, mixed> $data */
    public function createSeries(array $data): Series;

    /** @param array<string, mixed> $data */
    public function updateSeries(Series $series, array $data): Series;

    /** @param array<string, mixed> $filters */
    public function paginatePostsAdmin(array $filters, string $sort, int $perPage): LengthAwarePaginator;

    public function publishedCount(): int;

    /** @return Collection<int, Category> */
    public function categoriesForSelect(): Collection;

    /** @return Collection<int, Author> */
    public function authorsForSelect(): Collection;

    /** @return Collection<int, Tag> */
    public function tagsForSelect(): Collection;

    public function publishScheduledPosts(): int;

    /** @return Collection<int, Post> */
    public function searchPublished(string $query, int $limit): Collection;
}
