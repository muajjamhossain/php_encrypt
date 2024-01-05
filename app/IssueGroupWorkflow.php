<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 4/14/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class IssueGroupWorkflow extends Model
{
    protected $table ='issue_group_workflows';
    protected $primaryKey = 'issue_group_workflow_id';
    protected $fillable =[
                'issue_workflow_id',
                'group_info_id',
                'touch_maker',
                'touch_checker',
                'hold_maker',
                'hold_checker',
                'sla_maker',
                'sla_checker',
                'attach_maker',
                'attach_maker_item',
                'attach_checker',
                'attach_checker_item',
                'order_by',
                'is_touch_point'
    ];
    public function group_info()
    {
        return $this->belongsTo('App\GroupInfo','group_info_id','id');
    }
}