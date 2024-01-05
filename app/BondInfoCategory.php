<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BondInfoCategory extends Model
{
    protected $table = 'binfo_catsubcats';
    protected $fillable = ['parent_id', 'name', 'description',  'status', 'created_by', 'updated_by', 'ip'];
  
    public function bondInfoSubCategory()
	{
	  return $this->hasMany('App\BondInfoSubCategory','parent_id','id');
	}
}
