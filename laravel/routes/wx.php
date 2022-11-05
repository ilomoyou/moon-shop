<?php

use App\Http\Controllers\Wx\AddressController;
use App\Http\Controllers\Wx\AuthController;
use App\Http\Controllers\Wx\CatalogController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register']); // 账号注册
Route::post('auth/reg-captcha', [AuthController::class, 'regCaptcha']); // 注册验证码
Route::post('auth/login', [AuthController::class, 'login']); // 账号登录
Route::get('auth/info', [AuthController::class, 'info']); // 用户信息
Route::post('auth/logout', [AuthController::class, 'logout']); // 账号退出
Route::post('auth/reset', [AuthController::class, 'reset']); // 密码重置
Route::post('auth/profile', [AuthController::class, 'profile']); // 账号信息修改

# 用户模块-地址
Route::get('address/list', [AddressController::class, 'list']); // 收货地址列表
Route::get('address/detail', [AddressController::class, 'detail']); // 收货地址详情
Route::post('address/save', [AddressController::class, 'save']); // 保存收货地址
Route::post('address/delete', [AddressController::class, 'delete']); // 删除收货地址

# 商品模块-类目
Route::get('/catalog/index', [CatalogController::class, 'index']); // 分类目录全部分类
Route::get('/catalog/current', [CatalogController::class, 'current']); // 分类目录当前分类
