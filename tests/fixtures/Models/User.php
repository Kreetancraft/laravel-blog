<?php

namespace Kreetancraft\Blog\Tests\Fixtures\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * A host application's user model. This package does not ship one and does not
 * care which you use — the policy type-hints Authenticatable.
 */
class User extends Authenticatable
{
    use HasRoles;

    protected $table = 'users';

    protected $guarded = [];
}
