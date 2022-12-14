<?php

namespace Tests\Unit;

use App\Models\Goods;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BooleanSoftDeleteTest extends TestCase
{
    use DatabaseTransactions;

    private $goodsId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->goodsId = Goods::query()->insertGetId([
            "goods_sn" => "test",
            "name" => "轻奢纯棉刺绣水洗四件套",
            "category_id" => 1008009,
            "brand_id" => 0,
            "gallery" => '',
            "keywords" => "",
            "brief" => "设计师原款，精致绣花",
            "is_on_sale" => 1,
            "sort_order" => 23,
            "pic_url" => "https://yanxuan.nosdn.127.net/8ab2d3287af0cefa2cc539e40600621d.png",
            "share_url" => "",
            "is_new" => 0,
            "is_hot" => 0,
            "unit" => "件",
            "counter_price" => 919.0,
            "retail_price" => 899.0,
            "detail" => '',
        ]);
    }

    public function testSoftDeleteByModel()
    {
        $goods = Goods::query()->whereId($this->goodsId)->first();
        $goods->delete();
        $this->assertTrue($goods->deleted);
        $goods = Goods::query()->whereId($this->goodsId)->first();
        $this->assertNull($goods);

        $goods = Goods::onlyTrashed()->whereId($this->goodsId)->first();
        $goods->restore();
        $this->assertFalse($goods->deleted);
        $goods = Goods::query()->whereId($this->goodsId)->first();
        $this->assertEquals($this->goodsId, $goods->id ?? 0);
    }

    public function testSoftDeleteByBuilder()
    {
        $goods = Goods::query()->whereId($this->goodsId)->first();
        $this->assertEquals($this->goodsId, $goods->id ?? 0);

        // 获取包含没被软删除的数据
        $goods = Goods::withoutTrashed()->whereId($this->goodsId)->first();
        $this->assertEquals($this->goodsId, $goods->id ?? 0);

        // 软删除
        $ret = Goods::query()->whereId($this->goodsId)->delete();
        $this->assertEquals(1, $ret);

        // 再查询
        $goods = Goods::query()->whereId($this->goodsId)->first();
        $this->assertNull($goods);

        // 获取包含被软删除的数据
        $goods = Goods::withTrashed()->whereId($this->goodsId)->first();
        $this->assertEquals($this->goodsId, $goods->id ?? 0);

        // 只获取被软删除的数据
        $goods = Goods::onlyTrashed()->whereId($this->goodsId)->first();
        $this->assertEquals($this->goodsId, $goods->id ?? 0);

        // 恢复被软删除的数据
        $ret = Goods::withTrashed()->whereId($this->goodsId)->restore();
        $this->assertEquals(1, $ret);
        // 再此获取已被被软删除的数据
        $goods = Goods::onlyTrashed()->whereId($this->goodsId)->first();
        $this->assertNull($goods);
        // 再次正常查询
        $goods = Goods::query()->whereId($this->goodsId)->first();
        $this->assertEquals($this->goodsId, $goods->id ?? 0);

        // 硬删除（强制删除、物理删除）
        $ret = Goods::query()->whereId($this->goodsId)->forceDelete();
        $this->assertEquals(1, $ret);
        $goods = Goods::query()->whereId($this->goodsId)->first();
        $this->assertNull($goods);
        $goods = Goods::onlyTrashed()->whereId($this->goodsId)->first();
        $this->assertNull($goods);
    }
}
