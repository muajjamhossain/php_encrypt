<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = ['name', 'description',  'status', 'created_by', 'modified_by', 'ip'];
}
