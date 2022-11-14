<?php

use App\Http\Controllers\Wx\AddressController;
use App\Http\Controllers\Wx\AuthController;
use App\Http\Controllers\Wx\BrandController;
use App\Http\Controllers\Wx\CatalogController;
use App\Http\Controllers\Wx\CouponController;
use App\Http\Controllers\Wx\GoodsController;
use App\Http\Controllers\Wx\GrouponController;
use Illuminate\Support\Facades\Route;

# 用户模块-鉴权
Route::prefix('auth')->group(function () {
    Route::get('/info', [AuthController::class, 'info']); // 用户信息
    Route::post('/register', [AuthController::class, 'register']); // 账号注册
    Route::post('/reg-captcha', [AuthController::class, 'regCaptcha']); // 注册验证码
    Route::post('/login', [AuthController::class, 'login']); // 账号登录
    Route::post('/logout', [AuthController::class, 'logout']); // 账号退出
    Route::post('/reset', [AuthController::class, 'reset']); // 密码重置
    Route::post('/profile', [AuthController::class, 'profile']); // 账号信息修改
});

# 用户模块-地址
Route::prefix('address')->group(function () {
    Route::get('/list', [AddressController::class, 'list']); // 收货地址列表
    Route::get('/detail', [AddressController::class, 'detail']); // 收货地址详情
    Route::post('/save', [AddressController::class, 'save']); // 保存收货地址
    Route::post('/delete', [AddressController::class, 'delete']); // 删除收货地址
});

# 商品模块-类目
Route::prefix('catalog')->group(function () {
    Route::get('/index', [CatalogController::class, 'index']); // 分类目录全部分类
    Route::get('/current', [CatalogController::class, 'current']); // 分类目录当前分类
});

# 商品模块-品牌
Route::prefix('brand')->group(function () {
    Route::get('/list', [BrandController::class, 'list']); // 品牌列表
    Route::get('/detail', [BrandController::class, 'detail']); // 品牌详情
});

# 商品模块-商品
Route::prefix('goods')->group(function () {
    Route::get('/list', [GoodsController::class, 'list']); // 商品列表
    Route::get('/detail', [GoodsController::class, 'detail']); // 商品详情
    Route::get('/count', [GoodsController::class, 'count']); // 统计商品总数
    Route::get('/category', [GoodsController::class, 'category']); // 当前分类
});

# 营销模块-优惠券
Route::get('coupon/list', [CouponController::class, 'list']); // 优惠券列表
Route::get('coupon/my-list', [CouponController::class, 'myList']); // 我的优惠券列表
Route::post('coupon/receive', [CouponController::class, 'receive']); // 优惠券领取
// Route::get('coupon/select-list'); // 当前订单可用优惠券列表

Route::get('groupon/list', [GrouponController::class, 'list']); // 团购列表
