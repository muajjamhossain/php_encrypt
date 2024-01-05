<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BondInfoSubCategory extends Model
{
    protected $table = 'binfo_catsubcats';
    protected $fillable = ['parent_id', 'name', 'description',  'status', 'created_by', 'updated_by', 'ip'];
    
    public function bondInfoCategory()
    {
        return $this->belongsTo('App\BondInfoCategory','id');
    }
}
