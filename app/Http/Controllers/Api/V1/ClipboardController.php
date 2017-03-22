<?php

namespace Zync\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Zync\Http\Controllers\Controller;
use Zync\Helpers\Enums\ApiError;
use Zync\Kinds\User;
use Zync\Kinds\Clipboard;
use \Zync\Helpers\Utils;

class ClipboardController extends Controller {

	private static $REQUIRED_DATA = [
		'timestamp' => 'integer',
		'hash' => [
			'crc32' => 'string',
		],
		'encryption' => [
			'type' => 'AES256-GCM-NOPADDING',
			'iv' => 'string',
			'salt' => 'string'
		],
		'payload' => 'string',
		'payload-type' => 'TEXT|IMAGE|VIDEO|BINARY'
	];

	public function getClipboard(Request $request) {
		$user = User::getFromHeaderToken();
		$clipboard = $user->getClipboard();

		if(is_null($clipboard)){
			return response()->json(ApiError::$CLIPBOARD_EMPTY, 200);
		}

		return $clipboard->getLastClipboardContents();
	}

	public function getVerify(Request $request) {
		$user = User::getFromHeaderToken();
		$clipboard = $user->getClipboard();

		if(is_null($clipboard)){
			return response()->json(ApiError::$CLIPBOARD_EMPTY, 200);
		}

		return [
			"success" => true,
			"data" => $clipboard->getLastClipboardVerification()
		];
	}

	public function getClipboardWithTimestamp(Request $request, $timestamp) {
		$user = User::getFromHeaderToken();
		$clipboard = $user->getClipboard();

		if(is_null($clipboard)){
			return response()->json(ApiError::$CLIPBOARD_EMPTY, 200);
		}

		$contents = $clipboard->getTimestampClipboardContents($timestamp);

		if(is_null($contents)){
			return response()->json(ApiError::$CLIPBOARD_NOT_FOUND, 404);
		}

		return $clipboard->getTimestampClipboardContents($timestamp);
	}

	public function getVerifyWithTimestamp(Request $request, $timestamp) {
		$user = User::getFromHeaderToken();
		$clipboard = $user->getClipboard();

		if(is_null($clipboard)){
			return response()->json(ApiError::$CLIPBOARD_EMPTY, 200);
		}

		$verification = $clipboard->getTimestampClipboardVerification($timestamp);

		if(is_null($verification)){
			return response()->json(ApiError::$CLIPBOARD_NOT_FOUND, 404);
		}

		return [
			"success" => true,
			"data" => $verification
		];
	}

	public function postClipboard(Request $request) {
		$user = User::getFromHeaderToken();
		$clipboard = $user->getClipboard();

		$data = $request->json("data");

		if(is_null($data)){
			return response()->json(ApiError::$CLIPBOARD_INVALID, 400);
		}

		$difference = Utils::array_diff_key_recursive(self::$REQUIRED_DATA, $data);
		if(!is_null($difference)){
			return response()->json(ApiError::$CLIPBOARD_INVALID + [
				"error" => [
					"missing" => $difference
				]
			], 400);
		}

		$validation = Utils::array_validate_data_types(self::$REQUIRED_DATA, $data);
		if(!is_null($validation)){
			return response()->json(ApiError::$CLIPBOARD_INVALID + [
				"error" => [
					"invalid" => $validation
				]
			], 400);
		}

		$size = mb_strlen($data["payload"]);
		if($size > 10000000 && $data["timestamp"] < time() - Clipboard::EXPIRY_TIME_MAX){
			return response()->json(ApiError::$CLIPBOARD_LATE, 400);
		}else if($size < 10000000 && $data["timestamp"] < time() - Clipboard::EXPIRY_TIME_MIN){
			return response()->json(ApiError::$CLIPBOARD_LATE, 400);
		}

		if($data["timestamp"] > time()){
			return response()->json(ApiError::$CLIPBOARD_TIME_TRAVEL, 400);
		}

		if(!is_null($clipboard)){
			if($data["timestamp"] < $clipboard->getData()["timestamp"]){
				return response()->json(ApiError::$CLIPBOARD_OUTDATED, 400);
			}

			if($clipboard->exists($data["hash"]["crc32"])){
				return response()->json(ApiError::$CLIPBOARD_IDENTICAL, 400);
			}

			$clipboard->newClip($data);
			$clipboard->saveContents($data["payload"], $data["timestamp"]);
			$clipboard->save();
		}else{
			$clipboard = Clipboard::create($user->getData()->key()->pathEndIdentifier(), $data);
			$clipboard->saveContents($data["payload"], $data["timestamp"]);
		}

		return response()->json(["success" => true]);
	}

	public function getHistory(Request $request) {
		$user = User::getFromHeaderToken();
		$clipboard = $user->getClipboard();

		if(is_null($clipboard)){
			return [
				"success" => true,
				"data" => [
					"history" => []
				]
			];
		}

		return [
			"success" => true,
			"data" => [
				"history" => $clipboard->getHistory()
			]
		];
	}

}
