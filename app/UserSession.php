<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 3/26/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $primaryKey = 'user_id';
    protected $fillable = ['user_id', 'session_id'];
    public $timestamps = false;
}