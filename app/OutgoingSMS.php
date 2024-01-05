<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutgoingSMS extends Model
{
	protected $table = 'outgoingsmstable';
	public $timestamps = false;
    protected $fillable = ['id','sentSMSid','message','savetime','senttime','status','mobileNo','reference_number'];


}
