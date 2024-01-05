<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BondInformation extends Model
{
    protected $table = 'bond_informations';
    protected $fillable = ['binfo_category_id','binfo_subcategory_id','title','description','file_name', 'status','created_by','updated_by','ip'];
}
