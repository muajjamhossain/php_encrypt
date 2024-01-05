<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CIFParentUrl extends Model
{
	protected $table = 'cif_parent_url';
    protected $fillable = ['id','name','details','status','type'];
}
