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

$app->get("clipboard", "Zync\Http\Controllers\Api\V1\ClipboardController@getClipboard");
$app->get("clipboard/verify", "Zync\Http\Controllers\Api\V1\ClipboardController@getVerify");
$app->get("clipboard/history", "Zync\Http\Controllers\Api\V1\ClipboardController@getHistory");
$app->get("clipboard/{timestamp}", "Zync\Http\Controllers\Api\V1\ClipboardController@getClipboardWithTimestamp");
$app->get("clipboard/{timestamp}/verify", "Zync\Http\Controllers\Api\V1\ClipboardController@getVerifyWithTimestamp");
$app->post("clipboard", "Zync\Http\Controllers\Api\V1\ClipboardController@postClipboard");