<?php

namespace Kreetancraft\Blog\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Kreetancraft\Blog\Models\Category;

class CategoryPolicy extends BlogPolicy
{
    public const PERMISSION_SUBJECT = 'blog-category';

    public function viewAny(Authenticatable $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(Authenticatable $user, ?Category $category = null): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(Authenticatable $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(Authenticatable $user, ?Category $category = null): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(Authenticatable $user, ?Category $category = null): bool
    {
        return $this->allows($user, 'delete');
    }
}
