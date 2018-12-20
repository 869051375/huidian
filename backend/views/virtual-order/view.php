<?php
/* @var $this yii\web\View */
/* @var $model \common\models\VirtualOrder */

use common\utils\Decimal;
use yii\helpers\Url;


?>
<!--虚拟订单信息-->
<div class="row">
    <div class="col-lg-12">
        <div class="tabs-container">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel-body" style="padding: 0;border: none;">
                        <div class="table-responsive" style="padding:0;">
                            <table class="table table-bordered" style="border: none;margin: 0;">
                                <thead>
                                <tr style="border-top: 1px solid #e7eaec;">
                                    <th style="width: 163px;">订单信息</th>
                                    <th class="text-center" style="width: 136px;">客户信息</th>
                                    <th class="text-center" style="width: 186px;">关联商机</th>
                                    <th style="width: 186px;">支付信息</th>
                                    <th class="text-center" style="width: 126px;">交易状态</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                    <!-- 订单信息 -->
                                    <td style="vertical-align: middle;">
                                        <p class="text-muted"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></p>
                                        <p>订单号：<?= $model->sn; ?></p>
                                        <p class="text-muted">服务开始时间：<?= $model->getFirstBeginService() ? $model->getFirstBeginService() : '--';?></p>
                                        <p class="text-muted"><?= $model->order->getSourceAppName();?></p>
                                        <p class="text-muted"><?= $model->order->is_proxy ? $model->order->creator_name.'后台新增' : '客户自主下单'; ?></p>
                                    </td>

                                    <!-- 客户信息 -->
                                    <td class="text-center" style="vertical-align: middle;">
                                        <p><?= $model->user->name; ?></p>
                                        <p><?= $model->user->phone; ?></p>
                                    </td>

                                    <!--关联商机-->
                                    <td class="text-right" style="vertical-align: middle; text-align: center;">
                                        <?php if($model->opportunities): ?>
                                            <?php foreach($model->opportunities as $opportunity): ?>
                                                <a href="<?= Url::to(['opportunity/view', 'id' =>isset($opportunity->id) ? $opportunity->id : 0 ])?>"><?= isset($opportunity->name) ? $opportunity->name :''; ?></a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>

                                    <!-- 支付信息 -->
                                    <td  style="vertical-align: middle;">
                                        <p>商品金额：<?= Decimal::formatCurrentYuan($model->total_original_amount, 2) ?></p>
                                        <?php if($model->package_id > 0): ?>
                                            <p>套餐优惠：<?= Decimal::formatCurrentYuan(-$model->package_remit_amount, 2, [], [], true) ?></p>
                                        <?php endif; ?>
                                        <?php if($model->wx_remit_amount > 0): ?>
                                            <p>微信下单优惠：<?= Decimal::formatCurrentYuan(-$model->wx_remit_amount, 2, [], [], true) ?></p>
                                        <?php endif; ?>
                                        <?php if(abs($model->adjust_amount) > 0): ?>
                                            <p>变动金额：<?= Decimal::formatCurrentYuan($model->adjust_amount, 2, [], [], true) ?></p>
                                        <?php endif; ?>
                                        <?php if($model->coupon_remit_amount > 0): ?>
                                            <p>优惠券金额：<?= Decimal::formatCurrentYuan($model->coupon_remit_amount, 2) ?></p>
                                        <?php endif; ?>
                                        <?php if(abs($model->adjust_amount) <= 0 && $model->wx_remit_amount <= 0 && $model->package_remit_amount <= 0 && $model->coupon_remit_amount <= 0): ?>
                                            <p>优惠金额：<?= Decimal::formatCurrentYuan('0.00', 2) ?></p>
                                        <?php endif; ?>
                                        <p>应付金额：<?= Decimal::formatCurrentYuan($model->total_amount); ?></p>
                                        <p>已付金额：<?= Decimal::formatCurrentYuan($model->payment_amount); ?></p>
                                        <p <?php if ($model->getPendingPayAmount()): ?>class="text-danger"<?php else:?>class="text-primary"<?php endif; ?>>
                                            未付金额：<?= Decimal::formatCurrentYuan($model->getPendingPayAmount()); ?>
                                        </p>
                                        <p>可分配回款金额：<?= Decimal::formatCurrentYuan($model->getTotalAmount()); ?></p>
                                        <p>申请中的金额：<?= Decimal::formatCurrentYuan($model->getAdjustTotalPrice()); ?></p>
                                        <?php if ($model->total_tax > 0): ?>
                                            <p class="text-muted">
                                                <small>(含税<?= Decimal::formatCurrentYuan($model->total_tax, 2) ?>)</small>
                                            </p>
                                        <?php endif; ?>
                                    </td>

                                    <!--以下是订单状态-->
                                    <td class="status" style="vertical-align: middle; text-align: center;">
                                        <?= $model->getPayStatus(); ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br>



