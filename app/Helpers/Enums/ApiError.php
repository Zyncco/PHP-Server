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
