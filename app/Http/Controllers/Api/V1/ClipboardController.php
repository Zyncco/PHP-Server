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
		'properties' => [
			'time' => 'integer',
			'hash' => [
				'crc32' => 'string',
			],
			'data' => 'string'
		]
	];

	public function getClipboard(Request $request) {
		$user = User::findByZyncToken($request->header("X-ZYNC-TOKEN"));
		$clipboard = $user->getClipboard();

		if(is_null($clipboard)){
			return response()->json(ApiError::$CLIPBOARD_EMPTY, 200);
		}

		return $clipboard->getLastClipboardContents();
	}

	public function getClipboardWithTimestamp(Request $request, $timestamp) {
		$user = User::findByZyncToken($request->header("X-ZYNC-TOKEN"));
		$clipboard = $user->getClipboard();

		if(is_null($clipboard)){
			return response()->json(ApiError::$CLIPBOARD_EMPTY, 204);
		}

		$contents = $clipboard->getTimestampClipboardContents($timestamp);

		if(is_null($contents)){
			return response()->json(ApiError::$CLIPBOARD_NOT_FOUND, 404);
		}

		return $clipboard->getTimestampClipboardContents($timestamp);
	}

	public function postClipboard(Request $request) {
		$user = User::findByZyncToken($request->header("X-ZYNC-TOKEN"));
		$clipboard = $user->getClipboard();

		$data = $request->json("data");

		if(is_null($data)){
			return response()->json(ApiError::$CLIPBOARD_INVALID, 400);
		}

		if(count(Utils::array_diff_key_recursive(self::$REQUIRED_DATA, $data)) > 0){
			return response()->json(ApiError::$CLIPBOARD_INVALID, 400);
		}

		$size = mb_strlen($data["properties"]["data"]);
		if($size > 10000000 && $data["properties"]["time"] < time() - Clipboard::EXPIRY_TIME_MAX){
			return response()->json(ApiError::$CLIPBOARD_LATE, 400);
		}else if($size < 10000000 && $data["properties"]["time"] < time() - Clipboard::EXPIRY_TIME_MIN){
			return response()->json(ApiError::$CLIPBOARD_LATE, 400);
		}

		if($data["properties"]["time"] > time()){
			return response()->json(ApiError::$CLIPBOARD_TIME_TRAVEL, 400);
		}

		if(!is_null($clipboard)){
			if($data["properties"]["time"] < $clipboard->getData()["time"]){
				return response()->json(ApiError::$CLIPBOARD_OUTDATED, 400);
			}

			if($clipboard->exists($data["properties"]["hash"]["crc32"])){
				return response()->json(ApiError::$CLIPBOARD_IDENTICAL, 400);
			}

			$clipboard->newClip($data);
			$clipboard->saveContents($data["properties"]["data"], $data["properties"]["time"]);
			$clipboard->save();
		}else{
			$clipboard = Clipboard::create($user->getData()->key()->pathEndIdentifier(), $data);
			$clipboard->saveContents($data["properties"]["data"], $data["properties"]["time"]);
		}

		return response()->json(["success" => true]);
	}

	public function getHistory(Request $request) {
		$user = User::findByZyncToken($request->header("X-ZYNC-TOKEN"));
		$clipboard = $user->getClipboard();

		if(is_null($clipboard)){
			return response()->json(ApiError::$CLIPBOARD_EMPTY, 200);
		}

		return [
			"success" => true,
			"data" => [
				"history" => $clipboard->getHistory()
			]
		];
	}

}
