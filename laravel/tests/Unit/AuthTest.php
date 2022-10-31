<?php

namespace Tests\Unit;

use App\Exceptions\BusinessException;
use App\Services\AuthService;
use App\util\ResponseCode;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function testCheckMobileSendCaptchaCount()
    {
        $mobile = '18897729025';
        foreach (range(0, 9) as $i) {
            $isPass = AuthService::getInstance()->checkMobileSendCaptchaCount($mobile);
        }
        $isPass = AuthService::getInstance()->checkMobileSendCaptchaCount($mobile);
        $this->assertFalse($isPass);

        $countKey = "register_captcha_count_${mobile}";
        Cache::forget($countKey);
        $isPass = AuthService::getInstance()->checkMobileSendCaptchaCount($mobile);
        $this->assertTrue($isPass);
    }

    public function testCheckCaptcha() {
        $mobile = '18897729025';
        $code = AuthService::getInstance()->setCaptcha($mobile);
        $isPass = AuthService::getInstance()->checkCaptcha($mobile, $code);
        $this->assertTrue($isPass);

        // 异常断言
        $this->expectExceptionObject(new BusinessException(ResponseCode::AUTH_CAPTCHA_UN_MATCH));
        AuthService::getInstance()->checkCaptcha($mobile, $code);
    }
}
