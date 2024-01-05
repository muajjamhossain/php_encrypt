<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $fillable = ['name','description',  'status', 'created_by', 'modified_by', 'ip'];
}
