<?php

namespace Kreetancraft\Blog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kreetancraft\Blog\Concerns\HasBlogImages;
use Kreetancraft\Blog\Database\Factories\PostFactory;
use Kreetancraft\Blog\Enums\CommentStatus;
use Kreetancraft\Blog\Enums\PostStatus;
use Kreetancraft\Seo\Concerns\HasSeo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasBlogImages;
    use HasFactory;
    use HasSeo;
    use HasSlug;
    use SoftDeletes;

    protected $table = 'blog_posts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'published_at',
        'author_id',
        'series_id',
        'order_in_series',
        'is_featured',
    ];

    public function seoDefaults(): array
    {
        return [
            'title' => $this->title,
            'description' => (string) ($this->excerpt ?: $this->content),
            'path' => str_replace('{slug}', $this->slug, config('seo.paths.blog_post')),
            'image' => $this->featuredUrl(),
        ];
    }

    public function seoAnalysisSource(): array
    {
        return [
            'title' => $this->title,
            'slug' => (string) $this->slug,
            'content' => (string) $this->content,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'order_in_series' => 'integer',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'blog_post_category', 'post_id', 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blog_post_tag', 'post_id', 'tag_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('status', CommentStatus::Approved->value);
    }

    /**
     * Publicly visible posts: published and the publish time has passed.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PostStatus::Published->value)
            ->where('published_at', '<=', now());
    }

    /**
     * Scheduled posts whose publish time has arrived.
     */
    public function scopeScheduledDue(Builder $query): void
    {
        $query->where('status', PostStatus::Scheduled->value)
            ->where('published_at', '<=', now());
    }

    /**
     * URL of the featured image, served via /assets.
     */
    public function featuredUrl(): ?string
    {
        return $this->imageUrl((string) config('blog.collections.featured', 'featured'));
    }

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }
}
