<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DMSRequestResponse extends Model
{
	protected $table = 'dms_request_response';
    protected $fillable = ['id','reference_number','type','json_node','url','execution_time','global_transaction_id','cif_number','code','msg'];
}
