<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\BusinessException;
use App\Exceptions\ParametersException;
use App\Models\User;
use App\Services\AuthService;
use App\util\ResponseCode;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    protected $only = ['info', 'profile'];

    /**
     * 获取用户信息
     * @return JsonResponse
     */
    public function info()
    {
        $user = $this->user();
        return $this->success([
            'nickName' => $user->nickname,
            'avatar' => $user->avatar,
            'gender' => $user->gender,
            'mobile' => $user->mobile
        ]);
    }

    /**
     * 用户信息修改
     * @return JsonResponse
     * @throws ParametersException
     */
    public function profile()
    {
        $user = $this->user();
        $avatar = $this->verifyString('avatar');
        $gender = $this->verifyEnum('gender', 0, [0, 1, 2]);
        $nickname = $this->verifyString('nickname');

        if (!empty($avatar)) {
            $user->avatar = $avatar;
        }
        if (!empty($gender)) {
            $user->gender = $gender;
        }
        if (!empty($nickname)) {
            $user->nickname = $nickname;
        }
        $ret = $user->save();
        return $this->failOrSuccess($ret, ResponseCode::UPDATED_FAIL);
    }

    /**
     * 账号退出
     * @return JsonResponse
     */
    public function logout()
    {
        Auth::guard('wx')->logout();
        return $this->success();
    }

    /**
     * 密码重置
     * @return JsonResponse
     * @throws BusinessException
     * @throws ParametersException
     */
    public function reset()
    {
        $password = $this->verifyString('password');
        $mobile = $this->verifyMobilePhoneMust('mobile');
        $code = $this->verifyInteger('code');
        if (empty($password) || empty($mobile) || empty($code)) {
            return $this->fail(ResponseCode::PARAM_ILLEGAL);
        }

        $isPass = AuthService::getInstance()->checkCaptcha($mobile, $code);
        if (!$isPass) {
            return $this->fail(ResponseCode::AUTH_CAPTCHA_UN_MATCH);
        }

        $user = User::getByMobile($mobile);
        if (is_null($user)) {
            return $this->fail(ResponseCode::AUTH_MOBILE_UNREGISTERED);
        }

        $user->password = Hash::make($password);
        $ret = $user->save();
        return $this->failOrSuccess($ret, ResponseCode::UPDATED_FAIL);
    }

    /**
     * 账号登录
     * @param  Request  $request
     * @return JsonResponse
     * @throws ParametersException
     */
    public function login(Request $request)
    {
        $username = $this->verifyString('username');
        $password = $this->verifyString('password');
        if (empty($username) || empty($password)) {
            return $this->fail(ResponseCode::PARAM_ILLEGAL);
        }

        // 账号密码校验
        $user = User::getByUsername($username);
        if (is_null($user)) {
            return $this->fail(ResponseCode::AUTH_INVALID_ACCOUNT);
        }
        $isPass = Hash::check($password, $user->getAuthPassword());
        if (!$isPass) {
            return $this->fail(ResponseCode::AUTH_INVALID_ACCOUNT, '账号密码错误');
        }

        // 更新登录信息
        $user->last_login_time = now()->toDateTimeString();
        $user->last_login_ip = $request->getClientIp();
        if (!$user->save()) {
            return $this->fail(ResponseCode::UPDATED_FAIL);
        }

        // 生成token
        $token = Auth::guard('wx')->login($user);
        return $this->success([
            'token' => $token,
            'userInfo' => [
                'nickName' => $username,
                'avatarUrl' => $user->avatar
            ]
        ]);
    }

    /**
     * 用户注册
     * @param  Request  $request
     * @return JsonResponse
     * @throws BusinessException
     * @throws ParametersException
     */
    public function register(Request $request)
    {
        // 获取参数
        $username = $this->verifyString('username');
        $password = $this->verifyString('password');
        $mobile = $this->verifyMobilePhoneMust('mobile');
        $code = $this->verifyInteger('code');
        // 校验参数是否为空
        if (empty($username) || empty($password) || empty($mobile) || empty($code)) {
            return $this->fail(ResponseCode::PARAM_ILLEGAL);
        }

        // 校验用户是否存在
        $user = User::getByUsername($username);
        if (!is_null($user)) {
            return $this->fail(ResponseCode::AUTH_NAME_REGISTERED);
        }
        $user = User::getByMobile($mobile);
        if (!is_null($user)) {
            return $this->fail(ResponseCode::AUTH_MOBILE_REGISTERED);
        }

        // 校验验证码是否正确
        AuthService::getInstance()->checkCaptcha($mobile, $code);

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
     * @return array
     * @throws ParametersException
     */
    public function regCaptcha()
    {
        // 获取手机号
        $mobile = $this->verifyMobilePhoneMust('mobile');
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
        $isPass = AuthService::getInstance()->checkMobileSendCaptchaCount($mobile);
        if (!$isPass) {
            return $this->fail(ResponseCode::AUTH_CAPTCHA_FREQUENCY, '验证码发送频次超过当天限制');
        }

        // 设置验证码
        $code = AuthService::getInstance()->setCaptcha($mobile);
        // 发送短信
        AuthService::getInstance()->sendCaptchaMsg($mobile, $code);

        return $this->success();
    }
}
