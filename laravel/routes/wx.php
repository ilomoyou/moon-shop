<?php

use App\Http\Controllers\Wx\AddressController;
use App\Http\Controllers\Wx\AuthController;
use App\Http\Controllers\Wx\BrandController;
use App\Http\Controllers\Wx\CartController;
use App\Http\Controllers\Wx\CatalogController;
use App\Http\Controllers\Wx\CouponController;
use App\Http\Controllers\Wx\GoodsController;
use App\Http\Controllers\Wx\GrouponController;
use App\Http\Controllers\Wx\HomeController;
use App\Http\Controllers\Wx\OrderController;
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

# 订单模块-购物车
Route::prefix('cart')->group(function () {
    Route::get('index', [CartController::class, 'index']); // 获取购物车的数据
    Route::post('add', [CartController::class, 'add']); // 添加商品到购物车
    Route::post('fast-add', [CartController::class, 'fastAdd']); // 立即购买商品
    Route::post('delete', [CartController::class, 'delete']); // 删除购物车的商品
    Route::post('update', [CartController::class, 'update']); // 更新购物车的商品数量
    Route::post('checked', [CartController::class, 'checked']); // 选中或未选中商品
    Route::get('goods-count', [CartController::class, 'goodsCount']);// 获取购物车商品件数
    Route::get('checkout', [CartController::class, 'checkout']); // 下单前信息确认
});

Route::prefix('order')->group(function () {
    Route::post('submit', [OrderController::class, 'submit']); // 提交订单
    Route::post('cancel', [OrderController::class, 'cancel']); // 取消订单
    Route::post('refund', [OrderController::class, 'refund']); // 申请退款
    Route::post('confirm', [OrderController::class, 'confirm']); // 确定收货
    Route::post('delete', [OrderController::class, 'delete']); // 删除订单
    Route::get('detail', [OrderController::class, 'detail']); // 订单详情
    Route::get('list', [OrderController::class, 'list']); // 订单列表
    Route::post('h5pay', [OrderController::class, 'h5pay']); // 微信h5支付
    Route::post('wx-notify', [OrderController::class, 'wxNotify']); // 微信h5支付回调
});

// 团购列表
Route::get('groupon/list', [GrouponController::class, 'list']);

// 分享链接跳转
Route::get('/home/redirect-share-url', [HomeController::class, 'redirectShareUrl'])->name('home.redirectShareUrl');
