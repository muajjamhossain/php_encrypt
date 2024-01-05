<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CIFWorkflow extends Model
{
	protected $table = 'cif_workflow';
    protected $fillable = ['id','issue_id','group_info_id','status'];
}
