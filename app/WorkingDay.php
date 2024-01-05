<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkingDay extends Model
{
	// protected $table = 'holidays';
	public $timestamps = false;
    protected $fillable = ['id','day','months','year','dates','type','remarks','status']; 
}
