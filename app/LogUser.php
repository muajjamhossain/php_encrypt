<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class LogUser extends Model
{
    protected $table ='log_users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'ip', 'log_in_at'
    ];

    /**
     * Get the log_user that owns the user.
     */
    public function log_user()
    {
        return $this->belongsTo('App\User','user_id','id');
    }
}
