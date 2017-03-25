<?php

namespace Zync\Http\Middleware;

class CORS {

	public function handle($request, \Closure $next){
		$response = $next($request);
		$response->header('Access-Control-Allow-Origin', '*');
		return $response;
	}

}