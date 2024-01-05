<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 5/27/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class IssueConfig extends Model
{
    protected $table = "issue_config";
    protected $primaryKey = 'id';
    protected $fillable =[
        'issue_id',
        'label_name',
        'field_type',
        'field_name',
        'api_key',
        'options',
        'placeholder',
        'maximum_length',
        'minimum_length',
        'fixed_length',
        'is_required',

    ];
}