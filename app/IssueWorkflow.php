<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 4/14/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class IssueWorkflow extends Model
{
    protected $table ='issue_workflows';
    protected $primaryKey = 'issue_workflow_id';
    protected $fillable =[
            'flow_type',
            'issue_id',
            'log',
            'execute',
            'send_back',
            'cant_reach_to_customer',
            'complain_sla_time',
            'is_active',
    ];
    public function issue()
    {
        return $this->belongsTo('App\UnitItem','issue_id','id');
    }
}
