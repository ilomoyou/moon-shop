<?php

use App\Http\Controllers\Wx\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register']); // 账号注册
Route::post('auth/reg-captcha', [AuthController::class, 'regCaptcha']); // 注册验证码
Route::post('auth/login', [AuthController::class, 'login']); // 账号登录
Route::get('auth/info', [AuthController::class, 'info']); // 用户信息
Route::post('auth/logout', [AuthController::class, 'logout']); // 账号退出
Route::post('auth/reset', [AuthController::class, 'reset']); // 密码重置
Route::post('auth/profile', [AuthController::class, 'profile']); // 账号信息修改
