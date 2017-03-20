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
    return response("<img src='https://i.imgur.com/dglafLV.jpg'>");
});

$app->get("user/callback", function () {
    if (isset($_GET["token"])) {
        try {
            $google_tokens = json_decode(file_get_contents("https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com"), true);

            try{
	            $result = \Firebase\JWT\JWT::decode($_GET["token"], $google_tokens, ["RS256"]);
            }catch(Exception $e){
	            return response()->json(ApiError::$INVALID_TOKEN, 401);
            }

            if (!$result) {
                return response()->json(ApiError::$INVALID_TOKEN, 401);
            }

            $id_token = $_GET["token"];
            $email = $result->email;

            $user = \Zync\Kinds\User::findByEmail($email);
            $first = is_null($user);

            if ($first) {
                $data = [
                    "email" => $email,
                    "google_token" => $id_token,
                    "zync_token" => bin2hex(openssl_random_pseudo_bytes(16)),
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
        } catch (\Exception $exception) {
            return response()->json(ApiError::exception($exception), 500, [], JSON_UNESCAPED_SLASHES);
        }
    } else {
	    return response()->json(ApiError::$INVALID_TOKEN, 401);
    }
});