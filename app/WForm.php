<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WForm extends Model
{
	protected $table = 'w_form';
	protected $primaryKey = 'reference_number';
	public $incrementing = false;
	public $timestamps = false;
    protected $fillable = ['reference_number','account_number', 'customer_name', 'mobile_number', 'segment', 'product_type', 'item_type', 'priority', 'time_and_ext', 'source', 'tin_verified', 'caller_id', 'date_of_birth', 'mother_name', 'father_name', 'mobile_number2', 'address', 'other', 'dynamic_question', 'other2', 'notes', 'w_form_type', 'individual_acct_no','card_status']; 

    public function wFormType()
    {
        return $this->hasOne('App\WFormType','reference_number','reference_number');
    }
 	public function wFormAttachment()
    {
        return $this->hasMany('App\Attachment','reference_number','reference_number');
    }
    public function comment()
    {
        return $this->hasMany('App\Comment','reference_number','reference_number');
    }
    public function last_comment()
    {
        return $this->hasOne('App\Comment','reference_number','reference_number')->orderBy('id','DESC');
    }
    public function in_date_time()
    {
        return $this->hasOne('App\Comment','reference_number','reference_number')->where('isapproved','1')->orderBy('time','DESC');
    }
}
