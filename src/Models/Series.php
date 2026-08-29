<?php

namespace Kreetancraft\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kreetancraft\Blog\Concerns\HasBlogImages;
use Kreetancraft\Blog\Database\Factories\SeriesFactory;
use Kreetancraft\Blog\Enums\SeriesStatus;
use Kreetancraft\Seo\Concerns\HasSeo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Series extends Model
{
    use HasBlogImages;
    use HasFactory;
    use HasSeo;
    use HasSlug;

    protected $table = 'blog_series';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
    ];

    public function seoDefaults(): array
    {
        return [
            'title' => $this->title,
            'description' => (string) $this->description,
            'path' => str_replace('{slug}', $this->slug, config('seo.paths.blog_series')),
            'image' => $this->coverUrl(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SeriesStatus::class,
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->orderBy('order_in_series');
    }

    /**
     * URL of the series cover image, served via /assets.
     */
    public function coverUrl(): ?string
    {
        return $this->imageUrl((string) config('blog.collections.series_cover', 'cover'));
    }

    protected static function newFactory(): SeriesFactory
    {
        return SeriesFactory::new();
    }
}
