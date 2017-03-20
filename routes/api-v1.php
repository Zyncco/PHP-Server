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
    return (new \Zync\Helpers\DatastoreOld(env("GAE_APP_ID")))->listBooks();
});

$app->get("user/callback", function () {
    $client = new Google_Client();
    $client->setClientId(env("GAE_CLIENT_ID"));
    $client->setClientSecret(env("GAE_CLIENT_SECRET"));
    $client->setAccessType("offline");
    $client->setScopes([Google_Service_Plus::USERINFO_EMAIL]);
    $client->setIncludeGrantedScopes(true);
    $client->setRedirectUri((empty($_SERVER["HTTPS"]) ? "http" : "https") . '://' . $_SERVER['HTTP_HOST'] . '/api/v0/user/callback');

    if (isset($_GET["code"]) || isset($_GET["token"])) {
        try {
            $response = false;
            $id_token = false;

            if (isset($_GET["code"])) {
                $response = $client->authenticate($_GET['code']);
                $id_token = $response["id_token"];

                if (!$response) {
                    return response()->json(ApiError::INVALID_AUTH_CODE, 403);
                }
            } elseif (isset($_GET["token"])) {
                $response = $client->verifyIdToken($_GET["token"]);
                $id_token = $_GET["token"];

                if (!$response) {
                    return response()->json(ApiError::INVALID_ID_TOKEN, 403);
                }
            }

            $email = false;
            if (isset($_GET["code"])) {
                $email = (new Google_Service_Oauth2($client))->userinfo->get()->getEmail();
            } elseif (isset($_GET["token"])) {
                $email = $response["email"];
            }

            $user = \Zync\Kinds\User::findByEmail($email);
            $first = is_null($user);

            if ($first) {
                $data = [
                    "email" => $email,
                    "google_token" => $id_token,
                    "zync_token" => str_random(64),
                    "clip_count" => 0
                ];

                $user = \Zync\Kinds\User::create($data);
            } else {
                $user->setGoogleToken($id_token);
                $user->save();
            }

            return response()->json([
                "success" => true,
                "data" => [
                    "zync_token" => $user->getData()["zync_token"],
                    "first_device" => $first
                ]
            ]);
        } catch (Exception $exception) {
            return response()->json(ApiError::exception($exception), 500);
        }
    } else {
        return response()->json([
            "success" => true,
            "data" => [
                "location" => filter_var($client->createAuthUrl(), FILTER_SANITIZE_URL)
            ]
        ]);
    }
});

$app->get("user/auth", function () {
    $client = new Google_Client();
    $client->setClientId(env("GAE_CLIENT_ID"));
    $client->setClientSecret(env("GAE_CLIENT_SECRET"));
    $client->setAccessType("offline");
    $client->setScopes([Google_Service_Plus::USERINFO_EMAIL]);
    $client->setIncludeGrantedScopes(true);
    $client->setRedirectUri((empty($_SERVER["HTTPS"]) ? "http" : "https") . '://' . $_SERVER['HTTP_HOST'] . '/api/v0/user/callback');

    return response()->json([
        "success" => true,
        "data" => [
            "location" => filter_var($client->createAuthUrl(), FILTER_SANITIZE_URL)
        ]
    ]);
});