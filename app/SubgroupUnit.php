<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 3/19/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class SubgroupUnit extends Model
{
    protected $table = 'subgroup_units';
    protected $fillable =[
            'department_id',
            'group_info_id',
            'subgroup_info_id',
            'unit_id',
            'is_active'
        ];
    public function dept(){
        return $this->belongsTo('App\Department','department_id');
    }
    public function groupInfo(){
        return $this->belongsTo('App\GroupInfo','group_info_id');
    }
    public function subgroupInfo(){
        return $this->belongsTo('App\SubgroupInfo','subgroup_info_id');
    }
    public function unit(){
        return $this->belongsTo('App\Unit','unit_id');
    }

}