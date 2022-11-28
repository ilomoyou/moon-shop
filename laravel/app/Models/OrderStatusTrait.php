<?php


namespace App\Models;


use App\enum\OrderEnum;
use Exception;
use Illuminate\Support\Str;

/**
 * Trait OrderStatusTrait
 * @package App\Models
 * @method bool canCancelHandle 是否可以取消
 * @method bool canPayHandle 是否可支付
 * @method bool canShipHandle 是否可发货
 * @method bool canRefundHandle 是否可退款
 * @method bool canAgreeRefundHandle 是否可同意退款
 * @method bool canConfirmHandle 是否可确认收货
 * @method bool canCommentHandle 是否可评价
 * @method bool canRebuyHandle 是否可再次购买
 * @method bool canAfterSaleHandle 是否可售后
 * @method bool canDeleteHandle 是否可删除
 * @method bool isCreateStatus 订单创建状态
 * @method bool isCancelStatus 订单取消状态
 * @method bool isAutoCancelStatus 订单自动取消状态
 * @method bool isAdminCancelStatus 订单被管理员取消状态
 * @method bool isPayStatus 订单支付状态
 * @method bool isRefundStatus 订单退货状态
 * @method bool isRefundConfirmStatus 订单同意退货状态
 * @method bool isGrouponTimeoutStatus 订单团购超时状态
 * @method bool isShipStatus 订单发货状态
 * @method bool isConfirmStatus 订单确认收货状态
 * @method bool isAutoConfirmStatus 订单自动确认收货状态
 */
trait OrderStatusTrait
{
    /**
     * 订单对应状态的可操作选项
     * @var array[]
     */
    private $canHandleHap = [
        // 取消操作
        'cancel' => [
            OrderEnum::STATUS_CREATE
        ],
        // 删除操作
        'delete' => [
            OrderEnum::STATUS_CANCEL,
            OrderEnum::STATUS_AUTO_CANCEL,
            OrderEnum::STATUS_ADMIN_CANCEL,
            OrderEnum::STATUS_REFUND_CONFIRM,
            OrderEnum::STATUS_CONFIRM,
            OrderEnum::STATUS_AUTO_CONFIRM
        ],
        // 支付操作
        'pay' => [
            OrderEnum::STATUS_CREATE
        ],
        // 发货
        'ship' => [
            OrderEnum::STATUS_PAY
        ],
        // 评论操作
        'comment' => [
            OrderEnum::STATUS_CONFIRM,
            OrderEnum::STATUS_AUTO_CONFIRM
        ],
        // 确认收货操作
        'confirm' => [OrderEnum::STATUS_SHIP],
        // 取消订单并退款操作
        'refund' => [OrderEnum::STATUS_PAY],
        // 再次购买
        'rebuy' => [
            OrderEnum::STATUS_CONFIRM,
            OrderEnum::STATUS_AUTO_CONFIRM
        ],
        // 售后操作
        'after_sale' => [
            OrderEnum::STATUS_CONFIRM,
            OrderEnum::STATUS_AUTO_CONFIRM
        ],
        // 同意退款
        'agree_refund' => [
            OrderEnum::STATUS_REFUND
        ],
    ];

    /**
     * 利用魔术函数 + 反射 实现函数约定
     * can*Handle, is*Status 函数约定
     *
     * @param $name
     * @param $arguments
     * @return bool|mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        // 判断订单可操作选项方法: can*Handle
        if (Str::is('can*Handle', $name)) {
            if (is_null($this->order_status)) {
                throw new Exception("order status is null when call method[$name]!");
            }
            $key = Str::of($name)
                ->replaceFirst('can', '')
                ->replaceLast('Handle', '')
                ->snake();
            return in_array($this->order_status, $this->canHandleHap[(string) $key]);
        }
        // 判断订单状态是否正确方法: is*Status
        elseif (Str::is('is*Status', $name)) {
            if (is_null($this->order_status)) {
                throw new Exception("order status is null when call method[$name]!");
            }
            // 拼接订单对应状态的常量名称
            $key = Str::of($name)
                ->replaceFirst('is', '')
                ->replaceLast('Status', '')
                ->snake()->upper()->prepend('STATUS_');
            // 通过反射方式动态获取某个类的常量
            $status = (new \ReflectionClass(OrderEnum::class))->getConstant($key);
            return $this->order_status == $status;
        }

        return parent::__call($name, $arguments);
    }

    /**
     * 订单可执行的操作选项
     * @return array
     */
    public function getCanHandleOptions(): array
    {
        return [
            'cancel' => $this->canCancelHandle(),
            'delete' => $this->canDeleteHandle(),
            'pay' => $this->canPayHandle(),
            'comment' => $this->canCommentHandle(),
            'confirm' => $this->canConfirmHandle(),
            'refund' => $this->canRefundHandle(),
            'rebuy' => $this->canRebuyHandle(),
            'aftersale' => $this->canAfterSaleHandle(),
        ];
    }
}
