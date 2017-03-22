<?php

namespace Zync\Helpers\Enums;

class ApiError extends Enum {

    /**
     * 000 Block related to system
     */
    static $EXCEPTION_THROWN = ["success" => false, "error" => ["code" => 1]];

    /**
     * 100 Block related to user
     */
	static $INVALID_ZYNC_TOKEN = ["success" => false, "error" => ["code" => 100, "message" => "Invalid X-ZYNC-TOKEN"]];
	static $INVALID_TOKEN = ["success" => false, "error" => ["code" => 101, "message" => "Invalid Token"]];
	static $INVALID_AUTH_CODE = ["success" => false, "error" => ["code" => 102, "message" => "Invalid Auth Code"]];


    /**
     * 200 Block related to clipboard
     */
	static $CLIPBOARD_EMPTY = ["success" => false, "error" => ["code" => 200, "message" => "Clipboard Empty"]];
	static $CLIPBOARD_OUTDATED = ["success" => false, "error" => ["code" => 201, "message" => "Clipboard Outdated"]];
	static $CLIPBOARD_LATE = ["success" => false, "error" => ["code" => 202, "message" => "Clipboard Late"]];
	static $CLIPBOARD_IDENTICAL = ["success" => false, "error" => ["code" => 203, "message" => "Clipboard Identical"]];
	static $CLIPBOARD_INVALID = ["success" => false, "error" => ["code" => 204, "message" => "Clipboard Invalid"]];
	static $CLIPBOARD_TIME_TRAVEL = ["success" => false, "error" => ["code" => 205, "message" => "Clipboard is time traveling"]];
	static $CLIPBOARD_NOT_FOUND = ["success" => false, "error" => ["code" => 206, "message" => "Clipboard not found"]];
	static $CLIPBOARDS_NOT_FOUND = ["success" => false, "error" => ["code" => 207, "message" => "One or more of requested clipboards not found"]];

    /**
     * @param $exception \Exception
     * @return array
     */
    public static function exception($exception) {
        $response = ApiError::$EXCEPTION_THROWN;
        $response["error"]["message"] = $exception->getMessage() . " (". $exception->getFile() . ":" . $exception->getLine() . ")";
        return $response;
    }

}
