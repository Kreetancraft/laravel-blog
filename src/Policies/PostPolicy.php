<?php

namespace Kreetancraft\Blog\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Kreetancraft\Blog\Models\Post;

class PostPolicy extends BlogPolicy
{
    public const PERMISSION_SUBJECT = 'post';

    /**
     * Publishing is a separate right from editing: a writer may draft without
     * being able to put it in front of readers. Declared so it is generated —
     * an ability the policy checks but nobody can create fails for everyone.
     */
    public const PERMISSION_EXTRA_METHODS = ['publish'];

    public function viewAny(Authenticatable $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(Authenticatable $user, ?Post $post = null): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(Authenticatable $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(Authenticatable $user, ?Post $post = null): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(Authenticatable $user, ?Post $post = null): bool
    {
        return $this->allows($user, 'delete');
    }

    public function publish(Authenticatable $user, ?Post $post = null): bool
    {
        return $this->allows($user, 'publish');
    }
}
