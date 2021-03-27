<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class VerifyCode extends Model
{
    protected $table = 'verify_codes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id', 'code'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeInactiveUser($query, $code, $userId)
    {
        return $query->whereCode($code)->whereUserId($userId);
    }

    public static function verify($request)
    {
        $email = $request->input('email');
        $code = $request->input('code');
        $inactiveUser = User::checkInactive($email);
        if (!$inactiveUser) {
            return response()->json(['status' => false, 'message' => 'email is active'], Response::HTTP_UNAUTHORIZED);
        }

        $verifyCode = VerifyCode::inactiveUser($code, $inactiveUser->id)->first();

        if (!$verifyCode) {
            return response()->json(['status' => false, 'message' => 'code is expired'], Response::HTTP_BAD_REQUEST);
        }
        
        if (Carbon::parse(optional($verifyCode)->updated_at)->addMinutes(720)->isPast()) {
            return response()->json(['status' => false, 'message' => 'code is expired'], Response::HTTP_BAD_REQUEST);
        }
        $inactiveUser->update(['status' => config('common.account.status.active')]);
        return response()->json(['status' => true, 'message' => 'account is active'], Response::HTTP_BAD_REQUEST);
    }

    public static function send($email)
    {
        try {
            $inactiveUser = User::checkInactive($email);
            if (!$inactiveUser) return false;
            VerifyCode::updateOrCreate(['user_id' => $inactiveUser->id], ['code' => random_int(100000, 999999)]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
