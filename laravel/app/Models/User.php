<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [

    ];

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
}
