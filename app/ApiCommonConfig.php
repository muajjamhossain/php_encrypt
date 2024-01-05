<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiCommonConfig extends Model
{
	protected $table = 'api_common_config';
    protected $fillable = ['id','issue_id','api_parameter','value','type'];
}
