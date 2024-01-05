<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
	protected $fillable = [
        'name',
    	'address',
    	'company_vision', 
        'email',
        'mobile_no',
        'logo',
        'created_by',
        'modified_by',
    	'ip'
    ];
}
