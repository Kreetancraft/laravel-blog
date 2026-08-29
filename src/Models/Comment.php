<?php

namespace Kreetancraft\Blog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kreetancraft\Blog\Database\Factories\CommentFactory;
use Kreetancraft\Blog\Enums\CommentStatus;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'blog_comments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'parent_id',
        'user_id',
        'author_name',
        'author_email',
        'author_url',
        'content',
        'status',
        'ip_address',
        'user_agent',
        'referrer',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * The account that left this comment, when it was not a guest.
     *
     * Resolved from auth config rather than naming a class: this package does
     * not ship a user model and must work with whichever one you have.
     *
     * @return BelongsTo<Model, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', CommentStatus::Approved->value);
    }

    public function scopeByIp(Builder $query, string $ip): void
    {
        $query->where('ip_address', $ip);
    }

    /**
     * Display name: the linked user's name for authenticated comments,
     * otherwise the guest-supplied name.
     */
    public function displayName(): string
    {
        return $this->user?->name ?? $this->author_name ?? 'Anonymous';
    }

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }
}
