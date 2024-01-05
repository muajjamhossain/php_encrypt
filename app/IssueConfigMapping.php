<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IssueConfigMapping extends Model
{
	protected $table = 'issue_config_mapping';
    protected $fillable = ['id','issue_id','field_name','api_parameter','inquiry_field'];
}
