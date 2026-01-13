<?php

namespace App\Models\Traits;

use App\Models\Role;

trait HasRoles
{
    /**
     * Get roles
     * @return mixed
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    /**
     * Model has role
     * @param $role
     * @return bool
     */
    public function hasRole($role){
        return (bool) $this->roles()->where('slug', $role)->count();
    }
}
