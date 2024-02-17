<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;


class VerifyToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
			$arr = getallheaders();
			$all_req = $request->all();
			
			$junk = new \App\Junk();
			$junk->data = "ABC - ".json_encode($all_req);
			$junk->save();
			//$authHeader = $arr['Authorization'];
			$jwt = isset($all_req) && isset($all_req['Authorization']) ? $all_req['Authorization'] : null;
			
			//list($jwt) = sscanf( $authHeader, 'Bearer %s');
			if($jwt!=null)
                $user = JWTAuth::toUser($jwt);
            else
                $user = JWTAuth::toUser(null);
        }catch (JWTException $e) {
            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['status'=>422]);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['status'=>422]);
            }else{
                return response()->json(['status'=>422]);
            }
        }
        return $next($request);
    }
}