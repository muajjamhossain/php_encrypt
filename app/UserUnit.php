<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserUnit extends Model
{
    protected $fillable = ['user_id','unit_id','department_id','division_id'];
    public $timestamps = false;
}
