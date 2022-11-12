<?php


namespace App\util;


class ResponseCode
{
    // 通用返回码
    const SUCCESS = [0, '请求成功!'];
    const FAIL = [-1, '请求错误!'];
    const PARAM_ERROR = [400, '参数错误'];
    const PARAM_ILLEGAL = [401, '参数不合法'];
    const RESOURCE_NOT_FOUND = [404, '资源未找到'];
    const UN_AUTHORIZATION = [501, '未授权!'];
    const UPDATED_FAIL = [505, '更新数据失败'];

    // 业务返回码
    const AUTH_INVALID_ACCOUNT = [700, '账号密码异常'];
    const AUTH_CAPTCHA_FREQUENCY = [702, '验证码发送频繁，请稍后再试!'];
    const AUTH_CAPTCHA_UN_MATCH = [703, '验证码错误'];
    const AUTH_NAME_REGISTERED = [704, '用户已注册'];
    const AUTH_MOBILE_REGISTERED = [705, '手机号已注册'];
    const AUTH_MOBILE_UNREGISTERED = [706, '手机号未注册'];
    const AUTH_INVALID_MOBILE = [707, '手机号格式不正确'];
    const COUPON_EXCEED_LIMIT = [740, '优惠券已领完'];
    const COUPON_RECEIVE_FAIL = [741, '优惠券领取失败'];
}
