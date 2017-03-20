<?php

namespace Zync\Helpers\Enums;

class ApiError extends Enum {

    /**
     * 000 Block related to system
     */
    const EXCEPTION_THROWN = ["success" => false, "error" => ["code" => 1]];

    /**
     * 100 Block related to user
     */
    const INVALID_ID_TOKEN = ["success" => false, "error" => ["code" => 101, "message" => "Invalid ID Token"]];
    const INVALID_AUTH_CODE = ["success" => false, "error" => ["code" => 102, "message" => "Invalid Auth Code"]];


    /**
     * 200 Block related to clipboard
     */


    /**
     * @param $exception \Exception
     * @return array
     */
    public static function exception($exception) {
        $response = ApiError::EXCEPTION_THROWN;
        $response["error"]["message"] = $exception->getMessage();
        return $response;
    }

}
