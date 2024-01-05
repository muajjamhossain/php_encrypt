<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
	protected $table = 'reference';
    protected $fillable = ['id','reference_number','date','created_by','account_type','unit_id','subgroup_id','sub_group_info_id','status','form_status','memo','access_by','access_date'];
    public $timestamps = false;

    public function issueConfigForApi()
    {
        return $this
    			->hasMany('App\IssueConfig','issue_id','issue_id')
    			->where(function($q) {
					$q->where('api_key','<>','')
					  ->whereNotNull('api_key');
              	});
    }
}
