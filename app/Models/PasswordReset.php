<?php

namespace App\Models;

use App\Mail\ResetPasswordMail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class PasswordReset extends Model
{
    protected $table = 'password_resets';
    protected $primaryKey = 'id';
    protected $fillable = [
        'email', 'token'
    ];

    public static function sendMailReset($email)
    {
        $user = User::whereEmail($email)->first();
        if (!$user) {
            return response()->json(['status' => false], Response::HTTP_BAD_REQUEST);
        }
        $token = Str::random(60);
        try {
            PasswordReset::updateOrCreate(['email' => $email], ['token' => $token]);
            // send mail
            $objMail = new \stdClass();
            $objMail->url = "zing.vn";
            Mail::to($email)->send(new ResetPasswordMail($objMail));
            return response()->json(['status' => true], Response::HTTP_OK);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public static function reset($password, $token)
    {
        try {
            $passwordReset = PasswordReset::whereToken($token)->first();
            if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
                $passwordReset->delete();
                return response()->json(['status' => false], Response::HTTP_BAD_REQUEST);
            }
            User::whereEmail($passwordReset->email)->update(['password' => bcrypt($password)]);
            $passwordReset->delete();
            return response()->json(['status' => true], Response::HTTP_OK);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
