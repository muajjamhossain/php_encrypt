<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 3/26/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    protected $primaryKey = 'password_history_id';
    protected $table ='password_histories';
    protected $fillable =[
        'user_id',
        'password'
    ];
}