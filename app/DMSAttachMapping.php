<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DMSAttachMapping extends Model
{
	protected $table = 'dms_attach_mapping';
    protected $fillable = ['id','reference_number','attachment_id','doc_index','status']; 
}
