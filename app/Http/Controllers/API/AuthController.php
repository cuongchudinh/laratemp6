<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\PasswordReset;
use App\Models\User;
use App\Models\VerifyCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password'))
            ]);
            if (!VerifyCode::send($user->email)) {
                return response()->json(['status' => false, 'message' => 'send verify mail fail'], Response::HTTP_BAD_REQUEST);
            }
            return response()->json(['status' => true, 'data' => $user], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function verifyAccount(Request $request)
    {
        return VerifyCode::verify($request);
    }

    public function sendVerifyMail(Request $request)
    {
        $email = $request->email;
        if (!VerifyCode::send($email)) {
            return response()->json(['status' => false, 'message' => 'send verify mail fail'], Response::HTTP_BAD_REQUEST);
        }
        return response()->json(['status' => false, 'message' => 'send verify mail success'], Response::HTTP_BAD_REQUEST);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $isActive = User::checkActive($credentials['email']);
        if (!$isActive) {
            return response()->json(['status' => false, 'message' => 'email not yet active'], Response::HTTP_UNAUTHORIZED);
        }
        if (!Auth::attempt($credentials)) {
            return response()->json(['status' => false, 'message' => 'email or password is wrong!'], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $user = $request->user();
            $tokenResult = $user->createToken($user->email);
            $token = $tokenResult->token;
            $token->save();
            return response()->json(['status' => true, 'data' => ['token' => $tokenResult->accessToken]], Response::HTTP_OK);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function logout()
    {
        try {
            $user = auth()->user();
            $user->token()->revoke();
            return response()->json(['status' => true], Response::HTTP_OK);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        $user->password = bcrypt($request->input('password'));
        if (!$user->save()) {
            return response()->json(['status' => false], Response::HTTP_BAD_REQUEST);
        }
        return response()->json(['status' => true], Response::HTTP_OK);
    }

    public function sendMailResetPassword(Request $request)
    {
        $email = $request->input('email');
        return PasswordReset::sendMailReset($email);
    }

    public function resetPassword(Request $request)
    {
        $password = $request->input('password');
        $token = $request->input('token');
        return PasswordReset::reset($password, $token);
    }
}
