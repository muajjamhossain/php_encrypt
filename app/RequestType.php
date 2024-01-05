<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class RequestType extends Model
{
    protected $table = 'request_type';
    protected $primaryKey = 'id';
    protected $fillable =[
      'name',
      'status'
    ];
}
