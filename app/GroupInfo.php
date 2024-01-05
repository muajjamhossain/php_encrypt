<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class GroupInfo extends Model
{
    protected $table = 'group_info';
    protected $primaryKey = 'id';
    protected $fillable =[
      'name',
      'department_id',
      'description',
      'is_active',
        'group_level_id'
    ];

    public function dept(){
        return $this->belongsTo('App\Department','department_id');
    }
}
