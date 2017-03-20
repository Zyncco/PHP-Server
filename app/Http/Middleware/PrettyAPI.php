<?php

namespace Zync\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PrettyAPI {

	public function handle(Request $request, Closure $next){
		$response = $next($request);

		if(!is_null($request->get("pretty"))){
			$response->setEncodingOptions(JSON_PRETTY_PRINT);
		}

		return $response;
	}

}