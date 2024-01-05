<?php
/**
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com>.
 * User: Roy
 * Date: 3/8/2020
 * Time: 7:38 PM
 */
namespace App\Services;
class ProductTypeService
{
    public static function getAllProductType($sel=''){
        $rows = \App\ProductType::orderBy('name', 'ASC')->where('status',true)->get();
        $opt = '';
        foreach ($rows as $v) {
            $attr = ($v->id == $sel) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $v->id . '"' . $attr . '>' . $v->name. '</option>';
        }
        return $opt;
    }
	
	public static function getProductTypeByID($prodid=''){
        $rows = \App\ProductType::where('id', '=', $prodid)->get();
        $opt = '';
        foreach ($rows as $v) {
            $attr = ($v->id == $prodid) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $v->id . '"' . $attr . '>' . $v->name. '</option>';
        }
        return $opt;
    }
	
    public static function productTypes(){
        $rows = $rows = \App\ProductType::where('status',true)->get();
        return $rows;
    }
}