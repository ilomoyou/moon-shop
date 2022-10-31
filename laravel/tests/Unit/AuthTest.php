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
        $userService = new AuthService();
        foreach (range(0, 9) as $i) {
            $isPass = $userService->checkMobileSendCaptchaCount($mobile);
        }
        $isPass = $userService->checkMobileSendCaptchaCount($mobile);
        $this->assertFalse($isPass);

        $countKey = "register_captcha_count_${mobile}";
        Cache::forget($countKey);
        $isPass = $userService->checkMobileSendCaptchaCount($mobile);
        $this->assertTrue($isPass);
    }

    /**
     * @throws BusinessException
     */
    public function testCheckCaptcha() {
        $mobile = '18897729025';
        $userService = new AuthService();
        $code = $userService->setCaptcha($mobile);
        $isPass = $userService->checkCaptcha($mobile, $code);
        $this->assertTrue($isPass);

        // 异常断言
        $this->expectExceptionObject(new BusinessException(ResponseCode::AUTH_CAPTCHA_UN_MATCH));
        $userService->checkCaptcha($mobile, $code);
    }
}
