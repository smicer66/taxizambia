<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CORS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        header("Access-Control-Allow-Origin: *");

        // ALLOW OPTIONS METHOD	X-Auth-Token, 
        $headers = [
            'Access-Control-Allow-Methods'=> 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers'=> 'Content-Type, Origin, Authorization, Accept-Language, Accept'
        ];
	
        if($request->getMethod() == "OPTIONS") {
            // The client-side application can set only headers allowed in Access-Control-Allow-Headers

            return Response::make('OK', 200, $headers);
        }


        $response = $next($request);
        foreach($headers as $key => $value)
            $response->header($key, $value);
        return $response;
    }
}
