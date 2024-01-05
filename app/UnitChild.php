<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitChild extends Model
{
    protected $fillable = ['unit_id', 'department_id'];
    protected $table = 'unit_childs';
    public $timestamps = false;

    public function department()
    {
        return $this->belongsTo('App\Department','department_id','id');
    }
    public function units()
	{
	  return $this->belongsTo('App\Unit');
	}
}
