<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /** @var User $user */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * 获取 Token
     * @return string[]
     */
    public function getAuthHeader($username = 'user123', $password = 'user123')
    {
        $response = $this->post('wx/auth/login', [
            'username' => $username,
            'password' => $password,
        ]);
        $token = $response->getOriginalContent()['data']['token'] ?? '';
        return ['Authorization' => "Bearer ${token}"];
    }
}
