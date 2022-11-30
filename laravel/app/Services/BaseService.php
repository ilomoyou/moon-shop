<?php


namespace App\Services;


use Mockery;

class BaseService
{
    protected static $instance = [];

    /**
     * 服务层的单例模式
     * @return mixed|static
     */
    public static function getInstance()
    {
        if ((static::$instance[static::class] ?? null) instanceof static) {
            return static::$instance[static::class];
        }
        return static::$instance[static::class] = new static();
    }

    /**
     * 单测Mockery
     * @return Mockery\Mock
     */
    public static function mockInstance()
    {
        return static::$instance[static::class] = Mockery::mock(static::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    // 私有化 __construct 和 __clone 方法 禁止外部实例化实现单例
    private function __construct()
    {
    }

    private function __clone()
    {
    }
}
