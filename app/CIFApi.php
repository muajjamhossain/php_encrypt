<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CIFApi extends Model
{
	protected $table = 'cif_api';
    protected $fillable = ['id','issue_id','parent_api','type'];
}
