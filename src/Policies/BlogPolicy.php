<?php

namespace Kreetancraft\Blog\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Shared authorization behaviour for this package's policies.
 *
 * This package manages no roles or permissions. Its screens ask Laravel the
 * ordinary question and these policies answer it, so there are two clean ways
 * to control access: install kreetancraft/laravel-user-management, which
 * discovers each policy through Gate::policies() and creates the abilities from
 * the PERMISSION_SUBJECT below; or replace a policy outright with
 * Gate::policy() in your own provider.
 *
 * Installed on its own with no permissions anywhere, the screens are open —
 * there is nothing to enforce yet. Enforcement begins the moment permissions
 * exist.
 */
abstract class BlogPolicy
{
    use HandlesAuthorization;

    /**
     * The ability name for an action, e.g. `view-posts`.
     */
    public function ability(string $action): string
    {
        $plural = defined(static::class.'::PERMISSION_SUBJECT_PLURAL')
            ? constant(static::class.'::PERMISSION_SUBJECT_PLURAL')
            : Str::plural(constant(static::class.'::PERMISSION_SUBJECT'));

        return $action.'-'.Str::kebab((string) $plural);
    }

    protected function allows(Authenticatable $user, string $action): bool
    {
        if (! method_exists($user, 'can')) {
            return true;
        }

        if ($user->can($this->ability($action))) {
            return true;
        }

        return ! $this->permissionsInUse();
    }

    /**
     * Whether this application uses permissions at all.
     *
     * Checked system-wide: once any permission exists the application is using
     * them, and an ability nobody created must read as denied rather than
     * unconfigured. Deliberately forgiving — a missing package, table or
     * database all mean "not in use", so a fresh install boots open rather than
     * locking everyone out of the screen that would fix it.
     */
    private function permissionsInUse(): bool
    {
        if (! class_exists(Permission::class)) {
            return false;
        }

        try {
            return Permission::query()->exists();
        } catch (\Throwable) {
            return false;
        }
    }
}
