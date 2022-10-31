<?php


use App\Services\AuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function testRegisterErrCode()
    {
        $data = [
            'username' => '隔壁老王',
            'password' => '123456',
            'mobile' => '18111111111',
            'code' => 123
        ];
        $response = $this->post('wx/auth/register', $data);
        $response->assertJson([
            'errno' => 703,
            'errmsg' => '验证码错误'
        ]);
    }

    public function testRegister()
    {
        $code = (new AuthService())->setCaptcha('18111111111');
        $data = [
            'username' => '隔壁老王',
            'password' => '123456',
            'mobile' => '18111111111',
            'code' => $code
        ];
        $response = $this->post('wx/auth/register', $data);
        $response->assertStatus(200);

        $ret = $response->getOriginalContent();
        $this->assertEquals(0, $ret['errno']);
        $this->assertNotEmpty($ret['data']);
    }

    public function testRegisterMobileRuleError()
    {
        $data = [
            'username' => '隔壁老王',
            'password' => '123456',
            'mobile' => '131111111110',
            'code' => '12345'
        ];
        $response = $this->post('wx/auth/register', $data);
        $response->assertStatus(200);
        $ret = $response->getOriginalContent();
        $this->assertEquals(707, $ret['errno']);
    }

    public function testRegCaptcha()
    {
        $response = $this->post('wx/auth/reg-captcha', ['mobile' => '18897729025']);
        $response->assertJson(['errno' => 0, 'errmsg' => '请求成功!']);
        $response = $this->post('wx/auth/reg-captcha', ['mobile' => '18897729025']);
        $response->assertJson(['errno' => 702, 'errmsg' => '验证码发送频繁，请稍后再试!']);
    }
}
