<?php


namespace App\Inputs;


class OrderSubmitInput extends Input
{
    public $cartId;
    public $addressId;
    public $couponId;
    public $userCouponId;
    public $grouponRulesId;
    public $grouponLinkId;
    public $message;

    protected function rules()
    {
        return [
            'cartId' => 'required|integer|min:0',
            'addressId' => 'required|integer|min:0',
            'couponId' => 'required|integer|min:0',
            'userCouponId' => 'integer',
            'grouponRulesId' => 'integer',
            'grouponLinkId' => 'integer',
            'message' => 'string',
        ];
    }
}
