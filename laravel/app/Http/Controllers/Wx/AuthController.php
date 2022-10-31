<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\BusinessException;
use App\Models\User;
use App\Rules\MobilePhone;
use App\Services\AuthService;
use App\util\ResponseCode;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * 用户注册
     * @param  Request  $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function register(Request $request)
    {
        // 获取参数
        $username = $request->input('username');
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');

        // 校验参数是否为空
        if (empty($username) || empty($password) || empty($mobile) || empty($code)) {
            return $this->fail(ResponseCode::PARAM_ILLEGAL);
        }

        // 校验用户是否存在
        $user = User::getByUsername($username);
        if (!is_null($user)) {
            return $this->fail(ResponseCode::AUTH_NAME_REGISTERED);
        }
        $validator = Validator::make(['mobile' => $mobile], ['mobile' => new MobilePhone]);
        if ($validator->fails()) {
            return $this->fail(ResponseCode::AUTH_INVALID_MOBILE);
        }
        $user = User::getByMobile($mobile);
        if (!is_null($user)) {
            return $this->fail(ResponseCode::AUTH_MOBILE_REGISTERED);
        }

        // 校验验证码是否正确
        (new AuthService())->checkCaptcha($mobile, $code);

        // 写入用户表
        $user = new User();
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->avatar = "https://yanxuan.nosdn.127.net/80841d741d7fa3073e0ae27bf487339f.jpg?imageView&quality=90&thumbnail=64x64";
        $user->nickname = $username;
        $user->last_login_time = Carbon::now()->toDateTimeString(); // 'Y-m-d H:i:s'
        $user->last_login_ip = $request->getClientIp();
        $user->save();

        // todo 新用户发券

        // 返回用户信息 todo 返回token
        return $this->success([
            'token' => '',
            'userInfo' => [
                'nickname' => $username,
                'avatar' => $user->avatar
            ]
        ]);
    }

    /**
     * 发送注册短信验证码
     * @param  Request  $request
     * @return array
     */
    public function regCaptcha(Request $request)
    {
        // 获取手机号
        $mobile = $request->input('mobile');

        // 验证手机号是否合法
        if (empty($mobile)) {
            $this->fail(ResponseCode::PARAM_ILLEGAL);
        }
        $validator = Validator::make(['mobile' => $mobile], ['mobile' => new MobilePhone]);
        if ($validator->fails()) {
            return $this->fail(ResponseCode::AUTH_INVALID_MOBILE);
        }

        // 验证手机号是否已经被注册
        $user = User::getByMobile($mobile);
        if (!is_null($user)) {
            $this->fail(ResponseCode::AUTH_MOBILE_REGISTERED);
        }

        // 防刷验证 一分钟内只能请求一次，当天只能请求10次
        $lock = Cache::add("register_captcha_lock_${mobile}", 1, 60);
        if (!$lock) {
            return $this->fail(ResponseCode::AUTH_CAPTCHA_FREQUENCY);
        }
        $isPass = (new AuthService())->checkMobileSendCaptchaCount($mobile);
        if (!$isPass) {
            return $this->fail(ResponseCode::AUTH_CAPTCHA_FREQUENCY, '验证码发送频次超过当天限制');
        }

        // 设置验证码
        $code = (new AuthService())->setCaptcha($mobile);
        // 发送短信
        (new AuthService())->sendCaptchaMsg($mobile, $code);

        return $this->success();
    }
}
