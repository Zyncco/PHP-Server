<?php

namespace Zync\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Zync\Helpers\Enums\ApiError;
use \Zync\Kinds\User;

class XZyncToken {

	public function handle(Request $request, Closure $next){
		$token = $request->header("X-ZYNC-TOKEN");

		if(is_null($token) || is_null(User::findByZyncToken($token))){
			return response()->json(ApiError::$INVALID_ZYNC_TOKEN, 403);
		}

		return $next($request);
	}

}
