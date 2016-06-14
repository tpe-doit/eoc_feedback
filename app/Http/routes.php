<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () {
    return "Hello World";
});
$app->get('fetchEOC', 'CaseController@fetchEOCdata');
$app->get('feedback', 'FeedbackController@fetchAll');
$app->get('feedback/{CaseSN}', 'FeedbackController@fetch');
$app->post('feedback', 'FeedbackController@create');
