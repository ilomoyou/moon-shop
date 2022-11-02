<?php


use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
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
        $code = AuthService::getInstance()->setCaptcha('18111111111');
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

    public function testLogin()
    {
        $response = $this->post('wx/auth/login', [
            'username' => 'test',
            'password' => '123456',
        ]);
        $response->assertJson([
            "errno" => 0,
            "errmsg" => "请求成功!",
            "data" => [
                "userInfo" => [
                    "nickname" => "test",
                    "avatar" => "https://yanxuan.nosdn.127.net/80841d741d7fa3073e0ae27bf487339f.jpg?imageView&quality=90&thumbnail=64x64"
                ]
            ]
        ]);
        echo $response->getOriginalContent()['data']['token'] ?? '';
        $this->assertNotEmpty($response->getOriginalContent()['data']['token'] ?? '');
    }

    public function testInfo()
    {
        $response = $this->post('wx/auth/login', [
            'username' => 'user123',
            'password' => 'user123',
        ]);
        $token = $response->getOriginalContent()['data']['token'] ?? '';
        $response2 = $this->get('wx/auth/info', ['Authorization' => "Bearer ${token}"]);
        $user = User::getByUsername('user123');
        $response2->assertJson([
            'data' => [
                'nickName' => $user->nickname,
                'avatar' => $user->avatar,
                'gender' => $user->gender,
                'mobile' => $user->mobile
            ]
        ]);
    }

    public function testLogout()
    {
        $response = $this->post('wx/auth/login', [
            'username' => 'user123',
            'password' => 'user123',
        ]);
        $token = $response->getOriginalContent()['data']['token'] ?? '';
        $response2 = $this->get('wx/auth/info', ['Authorization' => "Bearer ${token}"]);
        $user = User::getByUsername('user123');
        $response2->assertJson([
            'data' => [
                'nickName' => $user->nickname,
                'avatar' => $user->avatar,
                'gender' => $user->gender,
                'mobile' => $user->mobile
            ]
        ]);
        $response3 = $this->post('wx/auth/logout', ['Authorization' => "Bearer ${token}"]);
        $response3->assertJson(['errno' => 0]);
        $response4 = $this->get('wx/auth/info', ['Authorization' => "Bearer ${token}"]);
        $response4->assertJson(['errno' => 501]);
    }

    public function testRest()
    {
        $mobile = '18897729026';
        $password = '123';
        $code = AuthService::getInstance()->setCaptcha($mobile);
        $response = $this->post('wx/auth/reset', [
            'mobile' => $mobile,
            'password' => $password,
            'code' => $code
        ]);
        $response->assertJson(['errno' => 0]);
        $user = User::getByMobile($mobile);
        $isPass = Hash::check($password, $user->password);
        $this->assertTrue($isPass);
    }

    public function testProfile()
    {
        $response = $this->post('wx/auth/login', [
            'username' => 'user123',
            'password' => 'user123',
        ]);
        $token = $response->getOriginalContent()['data']['token'] ?? '';
        $response2 = $this->post('wx/auth/profile', [
            'avatar' => '',
            'gender' => 1,
            'nickname' => 'user1234'
        ], ['Authorization' => "Bearer ${token}"]);
        $user = User::getByUsername('user123');
        $this->assertEquals('user1234', $user->nickname);
        $this->assertEquals('1', $user->gender);
    }
}
