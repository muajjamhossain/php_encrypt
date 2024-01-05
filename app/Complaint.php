<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
	protected $table = 'complaint';
	protected $primaryKey = 'reference_number';
	public $incrementing = false;
	public $timestamps = false;
	
    protected $fillable = ['reference_number','account_number','customer_name','mobile_number','segment','product_type','complaint_type','priority','time_and_ext','complaint_details','caller_id','source','repeat_complaint','tin_verified','amount', 'individual_acct_no','card_status'];

    public function complaintAttachment()
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
