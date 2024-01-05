<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
	protected $table = 'source';
    protected $fillable = ['id','source_name'];
}
