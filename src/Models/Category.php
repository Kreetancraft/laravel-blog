<?php

namespace Kreetancraft\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kreetancraft\Blog\Concerns\HasBlogImages;
use Kreetancraft\Blog\Database\Factories\CategoryFactory;
use Kreetancraft\Seo\Concerns\HasSeo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasBlogImages;
    use HasFactory;
    use HasSeo;
    use HasSlug;
    use SoftDeletes;

    protected $table = 'blog_categories';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function seoDefaults(): array
    {
        return [
            'title' => $this->name,
            'description' => (string) $this->description,
            'path' => str_replace('{slug}', $this->slug, config('seo.paths.blog_category')),
            'image' => null,
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'blog_post_category', 'category_id', 'post_id');
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
