<?php

namespace Zync\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Zync\Http\Controllers\Controller;
use Exception;
use Zync\Helpers\Enums\ApiError;
use Zync\Kinds\User;
use Firebase\JWT\JWT;

class UserController extends Controller {

	public function authenticate(Request $request) {
		if ($request->has("token")) {
			try {
				$google_tokens = json_decode(file_get_contents("https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com"), true);

				try{
					$result = JWT::decode($request->get("token"), $google_tokens, ["RS256"]);
				}catch(Exception $e){
					return response()->json(ApiError::$INVALID_TOKEN, 401);
				}

				if (!$result) {
					return response()->json(ApiError::$INVALID_TOKEN, 401);
				}

				$id_token = $request->get("token");
				$email = $result->email;

				$user = User::findByEmail($email);
				$first = is_null($user);

				if ($first) {
					$data = [
						"email" => $email,
						"google_token" => $id_token,
						"zync_token" => bin2hex(openssl_random_pseudo_bytes(16)),
						"clip_count" => 0
					];

					$user = User::create($data);
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
				return response()->json(ApiError::exception($exception), 500, [], JSON_UNESCAPED_SLASHES);
			}
		}

		return response()->json(ApiError::$INVALID_TOKEN, 401);
	}

}
