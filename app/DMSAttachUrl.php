<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DMSAttachUrl extends Model
{
	protected $table = 'dms_attach_url';
    protected $fillable = ['id','name','url','request','status'];
}
