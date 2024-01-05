<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DynamicAPICredential extends Model
{
	protected $table = 'dynamic_api_credential';
    protected $fillable = ['id','api','username','password'];
}
