<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DMSCustMapping extends Model
{
	protected $table = 'dms_cust_mappings';
    protected $fillable = ['id','cif_number','cif_index','cc_index','casa_index','sme_loan_index','retail_loan_index','corporate_loan_index','status']; 
}
