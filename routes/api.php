<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::post('/groups/getGroups/','GroupInfoController@getGroup')->name('groupInfo.getGroup');
Route::post('add-to-group','WorkflowController@addToGroup');*/
//Route::get('delete-to-group','WorkflowController@deleteFoodMenu');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
