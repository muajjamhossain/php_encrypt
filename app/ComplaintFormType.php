<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComplaintFormType extends Model
{
	protected $table = 'complaint_form_type';
	protected $primaryKey = 'reference_number';
	public $incrementing = false;
	public $timestamps = false;
    protected $fillable = [
        'reference_number',
        'extra_field'];
}
