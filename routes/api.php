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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/getStates','API\MasterController@getStates')->name('getStates');
Route::get('/getCities','API\MasterController@getCities')->name('getCities');
Route::post('/update_courier_status','CourierController@updateCourierStatus')->name('update_courier_status');
Route::post('/update_notification_status','NotificationController@updateNotificationStatus')->name('update_notification_status');

