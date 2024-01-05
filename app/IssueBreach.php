<?php
/**
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com>.
 * User: Tanay
 * Date: 6/13/2020
 * Time: 4:05 AM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class IssueBreach extends Model
{
    protected $table ="issue_breaches";
    protected $fillable =[
        'issue_id',
        'reference_no',
        'division_id',
        'department_id',
        'group_id',
        'subgroup_id',
        'breach_time',
        'time',
        'is_sent_subgroup',
        'is_sent_group',
        'is_sent_department',
        'is_sent_division',
        'status',
    ];

}
