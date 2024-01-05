<?php 

namespace App;

use Spatie\Permission\Models\Permission AS BasePermission;

class Permission extends BasePermission
{
	protected $fillable = ['controller_name', 'name', 'display_name', 'description'];
}