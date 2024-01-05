<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
	public function __construct($reqFrom = "")
    {

    	if(!empty($reqFrom)) {
    		$this->table  = 'sequences_'.$reqFrom;
    	}
    }
    protected $table = 'sequences_sr';
    // protected $fillable = ['user_id','unit_id','department_id','division_id'];
    // public $timestamps = false;
}
