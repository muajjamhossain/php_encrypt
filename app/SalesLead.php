<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class SalesLead extends Model
{
    protected $table = 'sales_lead';
    protected $primaryKey = 'id';
    protected $fillable =[
      'name',
      'status'
    ];
}
