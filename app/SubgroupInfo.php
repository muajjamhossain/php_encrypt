<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class SubgroupInfo extends Model
{
    protected $table = 'subgroup_info';
    protected $primaryKey = 'id';
    protected $fillable =[
        'group_info_id',
        'department_id',
        'name',
        'description',
        'is_active'
    ];
    public function dept(){
        return $this->belongsTo('App\Department','department_id');
    }
    public function groupInfo(){
        return $this->belongsTo('App\GroupInfo','group_info_id');
    }
}
