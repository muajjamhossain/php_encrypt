<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CIFRequestResponse extends Model
{
	protected $table = 'cif_request_response';
    protected $fillable = ['id','reference_number','account_number','type','json_node','url','execution_time','requested_by','global_transaction_id','cif_number','status_code','status_msg'];
}
