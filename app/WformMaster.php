<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WformMaster extends Model
{
    protected $fillable = ['id','master_id', 'name',  'type'];
}
