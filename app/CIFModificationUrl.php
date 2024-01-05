<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CIFModificationUrl extends Model
{
	protected $table = 'cif_modification_url';
    protected $fillable = ['id','name','url','request','status','parent','date_format'];
}
