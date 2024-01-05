<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 5/18/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class AttachmentHistory extends Model
{
    protected $table ="attachment_histories";
    protected $primaryKey ="id";
    protected $fillable =[
        'reference_number',
        'user_id',
        'attachment_count',
        'issue_id'
    ];
}