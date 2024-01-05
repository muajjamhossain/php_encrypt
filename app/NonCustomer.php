<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 4/25/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class NonCustomer extends Model
{
    protected $table ="non_customers";
    protected $fillable = [
        'reference_number',
        'customer_name',
		'customer_address',
        'mobile_number',
        'customer_email',
		'customer_profession',
		'customer_dob',
        'caller_id',
        'time_and_ext',
        'type',
        'details',
        'forward_to',
        'employment_address',
        'salary_income',
        'service_length',
        'request_type',
        'sales_lead',
        'other_bank_loan',
        'other_bank_credit_card',
        'status',
        'created_by',
    ];
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
    public function issue_workflow()
    {
        return $this->hasOne('App\IssueWorkflow','issue_id','w_form_type');
    }
	public function nonCustomersAttachment()
    {
        return $this->hasMany('App\Attachment','reference_number','reference_number');
    }
	
}
