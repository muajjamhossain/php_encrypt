<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'description', 'division_id', 'status', 'created_by', 'modified_by', 'ip'];

    public function units()
    {
        return $this->hasMany('App\Unit', 'parent_id', 'id');
    }
    public function division()
    {
        return $this->hasOne('App\Division','id','division_id');
    }
}
