<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{

    public function redirect($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback($provider)
    {
        try {
            $getInfo = Socialite::driver($provider)->stateless()->user();
            $user = User::createSocialUser($getInfo, $provider);
            if (!Auth::loginUsingId($user->id))
                return response()->json(['status' => false, 'message' => 'login fail'], Response::HTTP_UNAUTHORIZED);

            $tokenResult = $user->createToken($user->provider_id);
            $token = $tokenResult->token;
            $token->save();
            return response()->json(['status' => true, 'data' => ['token' => $tokenResult->accessToken]], Response::HTTP_OK);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }

}
