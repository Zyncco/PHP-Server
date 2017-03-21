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

use \Zync\Helpers\Enums\ApiError;

$app->get('ping', function () {
    return response("<img src='https://i.imgur.com/dglafLV.jpg'>", 200, ["X-S-L-JACKSON" => 'https://i.imgur.com/dglafLV.jpg']);
});

$app->get("user/authenticate", "Zync\Http\Controllers\Api\V1\UserController@authenticate");