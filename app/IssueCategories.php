<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IssueCategories extends Model
{
    protected $fillable = ['issues_from','product_type_id','name','status'];
	public $timestamps = false;

	public function productType(){
        return $this->hasOne('App\ProductType','id','product_type_id');
    }
}
