<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
	protected $table = 'comments';
    protected $fillable = ['reference_number','comments','time','user_id','unit_id','action','duration_in_minutes','created_at','updated_at','ip','isapproved','issendback','sendbacksms'];
}

