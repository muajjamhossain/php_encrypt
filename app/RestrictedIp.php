<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RestrictedIp extends Model
{
    protected $table ='restricted_ips';
    protected $primaryKey = 'id';

    protected $fillable = [
        'ip',
        'user_id',
    ];
    public function user(){
        return $this->belongsTo('App\user','user_id','id');
    }

}
