<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return "Hello world";
});
Route::get('feedback', 'FeedbackController@fetchAll');
Route::get('feedback/{CaseSN}', 'FeedbackController@fetch');
Route::post('feedback', 'FeedbackController@create');
