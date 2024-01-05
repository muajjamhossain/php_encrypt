<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
    	'name',
    	'controller_name', 
    	'action_names',  
    	'status'
    ];
    public function user()
    {
        return $this->hasMany('App\User','id');
    }
    public function control()
    {
        return $this->hasOne('App\Control','module_id');
    }

}

