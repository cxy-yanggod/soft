<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class CheckAndRefreshToken extends BaseMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @param $guard
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response|mixed|\Symfony\Component\HttpFoundation\Response
     * @throws JWTException
     */
    public function handle($request, Closure $next, $role = null)
    {
        $data = ['code'=>401,'message'=>'no login'];
        try {
            // 解析token角色
            $tokenRole = $this->auth->parseToken()->getClaim('role');
        } catch (JWTException $e) {
            return response()->json($data);
            /**
             * token解析失败，说明请求中没有可用的token。
             * 为了可以全局使用（不需要token的请求也可通过），这里让请求继续。
             * 因为这个中间件的责职只是校验token里的角色。
             */
            return $next($request);
        }
        // 判断token角色。
        if ($tokenRole != $role) {
            return response()->json($data);
        }
        return $next($request);
    }
}
