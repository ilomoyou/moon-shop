<?php


namespace App\Services;


class BaseService
{
    protected static $instance;

    // 服务层的单例模式
    public static function getInstance()
    {
        if (static::$instance instanceof static) {
            return static::$instance;
        }
        static::$instance = new static();
        return static::$instance;
    }

    // 私有化 __construct 和 __clone 方法 禁止外部实例化实现单例
    private function __construct()
    {
    }

    private function __clone()
    {
    }
}
