<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'description',  'status', 'created_by', 'modified_by', 'ip'];
    protected $table = 'units';

    public function department()
    {
        return $this->belongsTo('App\Department','id');
    }
    public function unitChilds()
	{
	  return $this->hasMany('App\UnitChild','unit_id','id');
	}
}
