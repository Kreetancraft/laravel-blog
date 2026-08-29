<?php

namespace Kreetancraft\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kreetancraft\Blog\Concerns\HasBlogImages;
use Kreetancraft\Blog\Database\Factories\AuthorFactory;
use Kreetancraft\Seo\Concerns\HasSeo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Author extends Model
{
    use HasBlogImages;
    use HasFactory;
    use HasSeo;
    use HasSlug;

    protected $table = 'blog_authors';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'bio',
    ];

    public function seoDefaults(): array
    {
        return [
            'title' => $this->name,
            'description' => (string) $this->bio,
            'path' => str_replace('{slug}', $this->slug, config('seo.paths.blog_author')),
            'image' => $this->avatarUrl(),
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * URL of the author's avatar image, served via /assets.
     */
    public function avatarUrl(): ?string
    {
        return $this->imageUrl((string) config('blog.collections.author_avatar', 'avatar'));
    }

    protected static function newFactory(): AuthorFactory
    {
        return AuthorFactory::new();
    }
}
