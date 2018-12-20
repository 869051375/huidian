<?php
/* @var $this yii\web\View */
/* @var $order \common\models\Order */
/** @var \common\models\MessageRemind $messageRemind */

use common\models\BusinessSubject;
use common\utils\Decimal;
$pc_domain = rtrim(\common\models\Property::get('pc_domain'), '/');
$pc_domain = str_replace('www', 'admin', $pc_domain);
$name = '';
$department = '';
if($messageRemind->administrator)
{
    $name = $messageRemind->administrator ? $messageRemind->administrator->name : '';
    $department = $messageRemind->administrator->department ? $messageRemind->administrator->department->name : '';
}
?>
<p><?= $messageRemind->message;?></p>
<table><thead><tr><th>订单信息</th><th>商品信息</th><th>客服人员</th>
        <th>付款方式</th><th>支付信息</th><th>订单状态</th></tr></thead>
    <tbody>
    <tr>
        <td><p><?= Yii::$app->formatter->asDatetime($order->virtualOrder->created_at)?> </p>
            <p><?= $order->sn ?></p>
            <p><?= $order->getSourceAppName() ?></p>
        </td>
        <td>
            <p><strong><?= $order->product_name; ?></strong></p>
            <?php if ($order->district_id): ?>
                <p class="text-muted"><?= $order->province_name; ?>
                    -<?= $order->city_name; ?>-<?= $order->district_name; ?></p>
            <?php else: ?>
                <p class="text-muted"><?= $order->service_area; ?></p>
            <?php endif; ?>
            <p>
                <?php if (!empty($order->businessSubject)):?>
                    <?php if ($order->businessSubject->subject_type == BusinessSubject::SUBJECT_TYPE_DISABLED):?>
                        <?= $order->businessSubject->company_name; ?>
                    <?php else:?>
                        <?= $order->businessSubject->region; ?>
                    <?php endif;?>
                <?php endif;?>
            </p>
        </td>
        <td><?= $order->customer_service_name ?></td>
        <td>
            <?php if ($order->is_installment): ?>
                <p><strong>分期付款</strong></p>
            <?php else: ?>
                <p><strong>一次付款</strong></p>
            <?php endif; ?>

        </td>
        <td>
            <p>商品金额：<?= Decimal::formatCurrentYuan($order->original_price, 2) ?></p>
            <?php if($order->package_remit_amount > 0): ?>
                <p>套餐优惠：<?= Decimal::formatCurrentYuan(-$order->package_remit_amount, 2, [], [], true) ?></p>
            <?php endif; ?>
            <?php if($order->wx_remit_amount > 0): ?>
                <p>微信下单优惠：<?= Decimal::formatCurrentYuan(-$order->wx_remit_amount, 2, [], [], true) ?></p>
            <?php endif; ?>
            <?php if(abs($order->adjust_amount) > 0): ?>
                <p>变动金额：<?= Decimal::formatCurrentYuan($order->adjust_amount, 2, [], [], true) ?></p>
            <?php endif; ?>
            <?php if($order->coupon_remit_amount > 0): ?>
                <p class="text-danger">优惠券金额：<?= Decimal::formatCurrentYuan($order->coupon_remit_amount, 2) ?></p>
            <?php endif; ?>
            <?php if(abs($order->adjust_amount) <= 0 && $order->wx_remit_amount <= 0 && $order->package_remit_amount <= 0 && $order->coupon_remit_amount <= 0): ?>
                <p class="text-danger">优惠金额：<?= Decimal::formatCurrentYuan('0.00', 2) ?></p>
            <?php endif; ?>
            <p>应付金额：<?= Decimal::formatCurrentYuan($order->price, 2) ?></p>
            <?php if ($order->tax > 0): ?>
                <p class="text-muted">
                    <small>(含税<?= Decimal::formatCurrentYuan($order->tax, 2) ?>)</small>
                </p>
            <?php endif; ?>
            <?php if ($order->virtualOrder->isUnpaid() || $order->virtualOrder->isPendingPayment()): ?>
                <p>
                    已付金额：<?= $order->virtualOrder->payment_amount; ?></p>
                <p class="text-danger">
                    未付金额：<?= $order->virtualOrder->getPendingPayAmount(); ?></p>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($order->isRefundApply() || $order->isRefundAudit()): ?>
                <!--退款中的状态-->
                <p><strong><?= $order->getRefundStatusName() ?></strong></p>
                <p class="text-muted text-left">
                    <small>退款原因：<?= $order->getRefundReasonText() ?></small>
                </p>
                <?php if ($order->isRefundAudit()): ?>
                    <p class="text-muted text-left">
                    <small>
                        退款金额：<?= Yii::$app->formatter->asCurrency($order->refund_amount); ?></small>
                    </p><?php endif; ?>
                <?php if ($order->isRefundApply()): ?>
                    <p class="text-muted text-left">
                    <small>
                        要求退款：<?= Yii::$app->formatter->asCurrency($order->require_refund_amount); ?></small>
                    </p><?php endif; ?>
                <?php if (!empty($order->refund_remark)): ?>
                    <p class="text-muted text-left">
                    <small>说明：<?= $order->refund_explain; ?></small>
                    </p><?php endif; ?>
                <?php if ($order->isRefundAudit() && !empty($order->refund_remark)): ?>
                    <p class="text-muted text-left">
                    <small>备注：<?= $order->refund_remark; ?></small>
                    </p><?php endif; ?>

            <?php elseif ($order->isInService()): ?>
                <?php if ($order->flow): ?>
                    <?php $flowHint = $order->getHintOperator($order->getCurrentNode()); ?>
                    <p>
                        <span class="<?php if ($order->isWarning()): ?>text-danger<?php endif; ?>"><?= $flowHint['content'] ?></span>
                    </p>
                <?php else: ?>
                    服务中
                <?php endif; ?>
            <?php elseif ($order->virtualOrder->isCanceled()): ?>
                <?php if($order->isBreakService() && $order->break_reason > 0):?>
                    <?= $order->getBreakReason()?>
                <?php else:?>
                    已取消
                <?php endif;?>
            <?php else: ?>
                <p>
                    <strong>
                        <?php if($order->isBreakService() && $order->break_reason > 0):?>
                            <?= $order->getBreakReason()?>
                        <?php else:?>
                            <?= $order->getStatusName(); ?>
                        <?php endif;?>
                    </strong>
                </p>

                <?php if ($order->isRefund()): ?>
                    <p class="text-muted"><?= $order->getRefundStatusName() ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    </tbody>
</table>
<br/><?= $messageRemind->created_at > 0 ? Yii::$app->formatter->asDatetime($messageRemind->created_at) : 0 ;?> 需由  <?= $department.'-'.$name;?><a href="<?= $pc_domain ?>/order/info?id=<?= $order->id?>">马上去处理！</a>





