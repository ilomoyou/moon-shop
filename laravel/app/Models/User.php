<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';

    public const CREATED_AT = 'add_time';

    public const UPDATED_AT = 'update_time';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'deleted'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [

    ];

    /**
     * 根据多个ID获取用户列表
     * @param  array  $userIds
     * @return User[]|Collection
     */
    public static function getUsersByIds(array $userIds)
    {
        if (empty($userIds)) {
            return collect([]);
        }
        return User::query()->whereIn('id', $userIds)->where('deleted', 0)->get();
    }

    /**
     * 根据用户名获取用户
     * @param $username
     * @return User|Model|null
     */
    public static function getByUsername($username)
    {
        return User::query()->where('username', $username)
            ->where('deleted', 0)->first();
    }

    /**
     * 根据手机号获取用户
     * @param $mobile
     * @return User|Model|null
     */
    public static function getByMobile($mobile)
    {
        return User::query()->where('mobile', $mobile)
            ->where('deleted', 0)->first();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'iss' => env('JWT_ISSUER'),
            'userId' => $this->getKey()
        ];
    }
}
