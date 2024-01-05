<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 5/7/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class FormStatus extends Model
{
    protected $table = 'form_status';
    protected $fillable =[
        'reference_number',
        'issue_id',
        'user_id',
        'in_time',
        'out_time',
        'form_status',
        'is_sendback'
    ];
}