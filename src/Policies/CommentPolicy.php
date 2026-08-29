<?php

namespace Kreetancraft\Blog\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Kreetancraft\Blog\Models\Comment;

class CommentPolicy extends BlogPolicy
{
    public const PERMISSION_SUBJECT = 'blog-comment';

    /**
     * Approving and rejecting is the whole job for a moderator, and separate
     * from being able to delete.
     */
    public const PERMISSION_EXTRA_METHODS = ['moderate'];

    public function viewAny(Authenticatable $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(Authenticatable $user, ?Comment $comment = null): bool
    {
        return $this->allows($user, 'view');
    }

    public function moderate(Authenticatable $user, ?Comment $comment = null): bool
    {
        return $this->allows($user, 'moderate');
    }

    public function delete(Authenticatable $user, ?Comment $comment = null): bool
    {
        return $this->allows($user, 'delete');
    }
}
