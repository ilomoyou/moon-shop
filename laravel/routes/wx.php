<?php

use App\Http\Controllers\Wx\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/reg-captcha', [AuthController::class, 'regCaptcha']);
