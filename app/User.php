<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Adldap\Laravel\Traits\HasLdapUser;

class User extends Authenticatable
{
    use Notifiable, HasLdapUser, HasRoles, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id', 'role_id', 'name', 'designation', 'national_id',
        'outlet_id', 'mobile_no', 'email', 'password',
        'status', 'profile_picture',
        'remember_token',
        'is_active',
        'is_block',
        'password_changed_at'

    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    /*public function roles()
    {
        return $this->belongsToMany('App\Role');
    }

    public function role_user()
    {
        return $this->hasOne('App\RoleUser');
    }*/

    /**
     * Get the user_type that owns the user.
     */
    public function user_type()
    {
        return $this->belongsTo('App\UserType');
    }
    /**
     * Get the branch_users that owns the user.
     */
    public function branch_users()
    {
        return $this->hasOne('App\BranchUser','user_id','id');
    }
    public function user_unit()
    {
        return $this->hasOne('App\UserUnit','user_id','id');
    }
    public function user_subgroupinfo()
    {
        return $this->hasOne('App\UserSubgroupInfo','user_id','id');
    }

    public function passwordHistories()
    {
        return $this->hasMany('App\PasswordHistory');
    }
}
