<?php


namespace App\Services;


use App\Exceptions\BusinessException;
use App\Notifications\VerificationCode;
use App\util\ResponseCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

class AuthService extends BaseService
{
    /**
     * 验证手机号发送验证码是否达到限制条数
     * @param  string  $mobile
     * @return bool
     */
    public function checkMobileSendCaptchaCount(string $mobile)
    {
        $countKey = "register_captcha_count_${mobile}";
        if (Cache::has($countKey)) {
            $count = Cache::increment($countKey);
            if ($count > 10) {
                return false;
            }
        } else {
            Cache::put($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
        }
        return true;
    }

    /**
     * 发送验证码短信 (第三方类库: easy-sms:easysms-notification-channel)
     * @param  string  $mobile
     * @param  string  $code
     */
    public function sendCaptchaMsg(string $mobile, string $code)
    {
        // 测试环境下不发送短信
        if (app()->environment('testing')) {
            return;
        }

        Notification::route(
            EasySmsChannel::class,
            new PhoneNumber($mobile, 86)
        )->notify(new VerificationCode($code));
    }

    /**
     * 验证短信验证码
     * @param  string  $mobile
     * @param  string  $code
     * @return bool
     * @throws BusinessException
     */
    public function checkCaptcha(string $mobile, string $code)
    {
        $key = "register_captcha_${mobile}";
        $isPass = $code === Cache::get($key);
        // 验证通过删除缓存
        if ($isPass) {
            Cache::forget($key);
            return true;
        } else {
            throw new BusinessException(ResponseCode::AUTH_CAPTCHA_UN_MATCH);
        }
    }

    /**
     * 设置手机短信验证码
     * @param  string  $mobile
     * @return string
     */
    public function setCaptcha(string $mobile)
    {
        // 随机生成6位验证码
        $code = strval(random_int(100000, 999999));
        // 验证码写入缓存 存储手机号和验证码之间的关联
        Cache::put("register_captcha_${mobile}", $code, 60 * 10); // 10分钟
        return $code;
    }
}
