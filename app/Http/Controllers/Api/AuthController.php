<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;


class AuthController extends Controller
{
    public function login(LoginRequest $request){

        $mobile = $request->validated()['mobile'];
        $user = User::firstWhere('mobile', $mobile);
        if (!$user){
            return response()->json(['message' => 'اطلاعات ورود صحیح نیست.'], 401);
        }

        $token =  $user->createToken('web-login');
        return response()->json([
            "message" => "ورود با موفقیت انجام شد.",
            'user' => $user,
            'token' => $token->plainTextToken,

        ],200);

    }
}
