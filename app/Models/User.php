<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
        'avatar',
        'provider',
        'provider_id',
        'role_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'provider_id', 'email_verified_at'
    ];

    /**
     * The attributes that should be filter for arrays.
     *
     * @var array
     */
    protected $filter = [
        'name',
        'email'
    ];

    public static function createSocialUser($getInfo, $provider)
    {
        $user = User::firstOrCreate(
            ['provider_id' => $getInfo->id],
            [
                'name'     => $getInfo->name,
                'email'    => $getInfo->email,
                'avatar'   => $getInfo->avatar,
                'provider' => $provider,
                'status'   => config('common.account.status.active'),
            ]
        );
        return $user;
    }

    public static function checkActive($email)
    {
        if (!isset($email)) return null;
        $user = User::whereEmail($email)->active()->first();
        return (bool)$user;
    }

    public static function checkInactive($email)
    {
        if (!isset($email)) return null;
        $user = User::whereEmail($email)->inactive()->first();
        return $user;
    }

    public function scopeActive($query)
    {
        return $query->whereStatus(config('common.account.status.active'));
    }

    public function scopeInactive($query)
    {
        return $query->whereStatus(config('common.account.status.inactive'));
    }

    public function scopeFilter($query, $request)
    {
        $param = $request->all();
        foreach ($param as $field => $value) {
            if (empty($value)) continue;
            if (!in_array($field, $this->filter)) continue;
            $query->where($field, 'like', "%{$value}%");
        }
        return $query;
    }

    public function scopeKeywordSearch($query, $keyword, $fields = []){
        foreach ($fields as $field) {
            if (empty($keyword)) break;
            if (!in_array($field, $this->filter)) continue;
            $query->orWhere($field, 'like', "%{$keyword}%");
        }
        return $query;
    }
}
