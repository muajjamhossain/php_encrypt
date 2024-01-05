<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitItem extends Model
{
    protected $fillable = [
        'name',
        'product_type_id',
        'master_id',
        'issues_from',
        'unit_id',
        'auto_unit_id',
        'is_sent_sms',
		'issue_categories_id',
        'status',
        'is_api'
        ];
    public $timestamps = false;

    public function unit()
    {
        return $this->hasOne('App\Unit','id','unit_id');
    }
    public function unit_callback()
    {
        return $this->hasOne('App\Unit','id','auto_unit_id');
    }
    
    public function unitChilds()
	{
	  return $this->hasMany('App\UnitChild','unit_id','id');
	}

	public function productType(){
        return $this->hasOne('App\ProductType','id','product_type_id');
    }
	
	public function IssueCategories(){
        return $this->hasOne('App\IssueCategories','id','issue_categories_id');
    }
}
