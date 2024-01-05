<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TATBreach extends Model
{
	protected $table = 'tat_breaches';
    protected $fillable = ['id','reference_no','customer_name','request_type','queue_date','queue_time','status'];
}
