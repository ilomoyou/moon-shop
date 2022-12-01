<?php

namespace Tests;

use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Inputs\OrderSubmitInput;
use App\Models\GoodsProduct;
use App\Models\Order;
use App\Models\User;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\SystemService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $token;

    /** @var User $user */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth();
    }

    public function auth($user = null)
    {
        if (!is_null($user)) {
            $this->user = $user;
        } else {
            if (is_null($this->user)) {
                $this->user = User::factory()->create();
            }
        }
        return $this->token = \Auth::login($this->user);
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

    /**
     * @param  array[]  $options
     * @return Order
     * @throws BusinessException
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function getSimpleOrder(array $options = [[11.3, 2], [2.3, 1], [81.4, 4]])
    {
        $this->user = User::factory()->defaultAddress()->create();
        $this->auth();
        $address = AddressService::getInstance()->getUserAddress($this->user->id);

        foreach ($options as list($price, $num)) {
            /** @var GoodsProduct $product */
            $product = GoodsProduct::factory()->create(['price' => $price]);
            CartService::getInstance()->addCartOrBuyNow($this->user->id, $product->goods_id, $product->id, $num);
        }

        $input = OrderSubmitInput::new([
            'addressId' => $address->id,
            'cartId' => 0,
            'couponId' => 0,
            'grouponRulesId' => 0,
            'message' => '备注'
        ]);

        SystemService::mockInstance()->shouldReceive('getFreightValue')->andReturn(0);
        return OrderService::getInstance()->submitOrder($this->user->id, $input);
    }
}
