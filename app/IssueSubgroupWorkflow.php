<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 4/17/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class IssueSubgroupWorkflow extends Model
{
    protected $table = 'issue_subgroup_workflow';
    protected $primaryKey = 'issue_subgroup_workflow_id';
    protected $fillable =[
            'issue_workflow_id',
            'issue_id',
            'group_info_id',
            'subgroup_info_id',
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
    ];
}