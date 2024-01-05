<?php
/**
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com>.
 * User: Tanay
 * Date: 8/14/2020
 * Time: 4:30 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class ComplaintFormTypeHistory extends Model
{
    protected $table = 'complaint_form_type_histories';
    protected $fillable =[
        'reference_number',
        'extra_field',
        'check_list',
        'user_id'
    ];
}
