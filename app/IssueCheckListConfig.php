<?php
/**
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com>.
 * User: Tanay
 * Date: 7/13/2020
 * Time: 12:54 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class IssueCheckListConfig extends Model
{
    protected $table = "issue_check_list_config";
    protected $primaryKey = 'id';
    protected $fillable =[
        'issue_id',
        'label_name',
        'field_type',
        'field_name',
        'options',
        'placeholder',
        'maximum_length',
        'is_required',

    ];
}
