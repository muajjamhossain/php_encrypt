<?php
/**
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com>.
 * User: Tanay
 * Date: 6/17/2020
 * Time: 2:58 AM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class IssueBreachEmailHistory extends Model
{
    protected $table ='issue_breach_email_histories';
    protected $fillable =[
      'reference_no' ,
      'issue_id' ,
      'sent_group' ,
      'is_sent'
    ];
}
