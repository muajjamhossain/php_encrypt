<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutgoingEMAIL extends Model
{
	protected $table = 'outgoingemailtable';
	public $timestamps = false;
    protected $fillable = ['id','subject','body','savetime','senttime','status','email_address','reference_number'];


}
