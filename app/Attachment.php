<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
	protected $table = 'attachments';
	public $timestamps = false;
    protected $fillable = ['id','file_name','reference_number','attachment_date','uploaded_by']; 
}
