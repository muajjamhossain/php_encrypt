<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Control extends Model
{
    protected $fillable = [
        'module_id',
    	'module_name',
    	'user_id', 
        'status',
        'created_by',
        'modified_by',
    	'ip'
    ];
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function module()
    {
        return $this->hasOne('App\Module');
    }


}

