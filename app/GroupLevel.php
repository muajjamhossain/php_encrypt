<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 4/12/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class GroupLevel extends Model
{
    protected $table = 'group_levels';
    protected $primaryKey ='group_level_id';
    protected $fillable =[
      'name'
    ];
}