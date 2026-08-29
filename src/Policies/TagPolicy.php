<?php

namespace Kreetancraft\Blog\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Kreetancraft\Blog\Models\Tag;

class TagPolicy extends BlogPolicy
{
    public const PERMISSION_SUBJECT = 'blog-tag';

    public function viewAny(Authenticatable $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(Authenticatable $user, ?Tag $tag = null): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(Authenticatable $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(Authenticatable $user, ?Tag $tag = null): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(Authenticatable $user, ?Tag $tag = null): bool
    {
        return $this->allows($user, 'delete');
    }
}
