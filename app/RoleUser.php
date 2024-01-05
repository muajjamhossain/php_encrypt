<?php

namespace App;
use Illuminate\Database\Eloquent\Model;


class RoleUser extends Model
{
    protected $table = 'model_has_roles';

	protected $fillable = ['name', 'display_name', 'description'];
    public $timestamps = false;
	public function roles()
    {
        return $this->belongsTo('App\Role');
    }

}
