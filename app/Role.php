<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(config('auth.providers.users.model'));
    }

    //Zihad
    public function perms()
    {
        return $this->belongsToMany(config('permission.models.permission'), Config::get('permission.table_names.role_has_permissions'), 'role_id', 'permission_id');
    }
}
