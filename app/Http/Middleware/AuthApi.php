<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as smfRsponse;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use App\Services\Response;



class AuthApi
{
    public function handle(Request $request, Closure $next): smfRsponse
    {
        

        $token = $request->bearerToken();

        if (!$token){
            return Response::push( message:'Unauthorized' , status:401);
        }


        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || !($accessToken->tokenable instanceof User)) {
            return Response::push( message:'Invalid Token' , status:401);

        }


        $user = $accessToken->tokenable;

        $request->merge(['user' => $user]);

        return $next($request);
    
    }
}
