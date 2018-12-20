<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use common\utils\Decimal;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;

/** @var string $status */
/** @var ActiveDataProvider $dataProvider */
$models = $dataProvider ? $dataProvider->getModels() : [];
?>
<div class="wrapper wrapper-content animated fadeIn">
    <?= $this->render('info', ['customer' => $customer]) ?>
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <?= $this->render('nav-tabs', ['customer' => $customer]) ?>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <div class="col-lg-12">
                            <div class="tabs-container">
                                <ul class="nav nav-tabs">
                                    <li<?php if($status == 'paid'): ?> class="active"<?php endif; ?>>
                                        <a href="<?= \yii\helpers\Url::to(['order', 'status' => 'paid', 'id' => $customer->id])?>">已付款</a>
                                    </li>
                                    <li<?php if($status == 'pending-pay'): ?> class="active"<?php endif; ?>>
                                        <a href="<?= \yii\helpers\Url::to(['order', 'status' => 'pending-pay', 'id' => $customer->id])?>">待付款</a>
                                    </li>
                                    <li<?php if($status == 'break'): ?> class="active"<?php endif; ?>>
                                        <a href="<?= \yii\helpers\Url::to(['order', 'status' => 'break', 'id' => $customer->id])?>">服务终止</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active">
                                        <div class="panel-body">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>订单信息</th>
                                                    <th>商品信息</th>
                                                    <th>业务人员</th>
                                                    <th>客服</th>
                                                    <th>服务人员</th>
                                                    <th>订单金额</th>
                                                    <th>订单状态</th>
                                                    <th class="text-center">发票状态</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if(!empty($models)): ?>
                                                    <?php /** @var \common\models\Order $order */
                                                    foreach($models as $order): ?>
                                                        <tr>
                                                            <td>
                                                                <a <?php if (Yii::$app->user->can('order/info') && $order->hasDetail()): ?>href="<?= Url::to(['order/info', 'id' => $order->id]) ?>"<?php else: ?> class="add-color" <?php endif; ?> target="_blank"
                                                                   class="m-t-xs"><?= $order->sn ?></a><br>
                                                                <?= Yii::$app->formatter->asDatetime($order->created_at); ?>
                                                            </td>
                                                            <td>
                                                                <?= $order->product_name ?><br>
                                                                <?= $order->getArea() ?><br>
                                                                <?= $order->company_name ?>
                                                            </td>
                                                            <td><?= $order->salesman_name ?></td>
                                                            <td><?= $order->customer_service_name ?></td>
                                                            <td><?= $order->clerk_name ?></td>
                                                            <td style="color:red;"><?= Decimal::formatYenCurrent($order->virtualOrder->total_amount) ?></td>
                                                            <td style="color:#1d8468;"><?= $order->getStatus() ?></td>
                                                            <td>
                                                                <?php if ($order->invoice):?>
                                                                    <?= $order->invoice->getStatusName()?>
                                                                <?php else:?>
                                                                    未开票
                                                                <?php endif;?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center">暂无数据</td>
                                                    </tr>
                                                <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                <tr>
                                                    <td colspan="9">
                                                        <?php if($dataProvider): ?>
                                                        <?=
                                                        LinkPager::widget([
                                                            'pagination' => $dataProvider->pagination,
                                                        ]);
                                                        ?>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>