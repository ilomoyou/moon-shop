<?php


namespace App\util;


class ResponseCode
{
    // 通用返回码
    const SUCCESS = [0, '请求成功!'];
    const FAIL = [-1, '请求错误!'];
    const PARAM_ILLEGAL = [401, '参数不合法'];

    // 业务返回码
    const AUTH_CAPTCHA_FREQUENCY = [702, '验证码发送频繁，请稍后再试!'];
    const AUTH_CAPTCHA_UN_MATCH = [703, '验证码错误'];
    const AUTH_NAME_REGISTERED = [704, '用户已注册'];
    const AUTH_MOBILE_REGISTERED = [705, '手机号已注册'];
    const AUTH_INVALID_MOBILE = [707, '手机号格式不正确'];
}
