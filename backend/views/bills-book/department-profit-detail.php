<?php
/** @var $this \yii\web\View */
/* @var $provider \yii\data\ActiveDataProvider */
/** @var $model \common\models\CrmDepartment */
/** @var $correctPerformance \common\models\PerformanceStatistics[] */
/** @var $orderCalculateCollect \common\models\OrderPerformanceCollect */
/** @var $orderPerformanceCollect \common\models\OrderPerformanceCollect */
/** @var $year string */
/** @var $month string */
/** @var $profitRecord \common\models\MonthProfitRecord */

use backend\widgets\LinkPager;
use common\utils\Decimal;
use yii\helpers\Url;
$uniqueId = Yii::$app->controller->action->uniqueId;
/** @var \common\models\PerformanceStatistics[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
$date = Yii::$app->request->get('date');
?>
<?= $this->render('d-top',[
        'profitRecord' => $profitRecord,
        'year' => $year,
        'month' => $month,
        'model' => $model,
        'orderCalculateCollect' => $orderCalculateCollect,
        'orderPerformanceCollect' => $orderPerformanceCollect,
]) ?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="tabs-container">
                <div class="row">
                    <div class="col-lg-12">
                        <ul class="nav nav-tabs">
                            <li <?php if($uniqueId == 'bills-book/department-detail'): ?>class="active"<?php endif;?>>
                                <a href="<?= Url::to(['bills-book/department-detail','did' => $model->id,'date' => $date]) ?>">预计利润金额流水</a>
                            </li>
                            <li <?php if($uniqueId == 'bills-book/department-profit-detail'): ?>class="active"<?php endif;?>>
                                <a href="<?= Url::to(['bills-book/department-profit-detail','did' => $model->id,'date' => $date]) ?>">提成金额流水</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>提成计算历史</b>：</p>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 304px;">计算时间</th>
                                    <th class="text-center" style="width: 265px;">提点类型</th>
                                    <th class="text-center" style="width: 265px;">提点</th>
                                    <th class="text-center" style="width: 265px;">虚拟订单号</th>
                                    <th class="text-center" style="width: 265px;">子订单号</th>
                                    <th class="text-center" style="width: 265px;">金额归属对象</th>
                                    <th class="text-center" style="width: 265px;">已计算实际利润金额</th>
                                    <th class="text-center" style="width: 265px;">提成金额</th>
                                    <th class="text-center" style="width: 224px;">操作人</th>
                                </tr>
                                </thead>
                                <tbody id="cost-record-list">
                                <?php foreach ($models as $performance): ?>
                                    <tr style="background: none;">
                                        <td class="text-center"><?= Yii::$app->formatter->asDatetime($performance->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                        <td class="text-center"><?= $performance->getAlgorithmName(); ?></td>
                                        <td class="text-center"><?= floatval($performance->reward_proportion).'%'; ?></td>
                                        <td class="text-center">
                                            <?php if (Yii::$app->user->can('virtual-order-list/list')): ?>
                                                <a href="<?= Url::to(['virtual-order/order', 'vid' => $performance->order->virtual_order_id]) ?>" target="_blank"><?= $performance->order->virtualOrder->sn; ?></a>
                                            <?php else: ?>
                                                <?= $performance->order->virtualOrder->sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (Yii::$app->user->can('virtual-order-action/detail') || $performance->order->hasDetail()): ?>
                                                <a href="<?= Url::to(['order/info', 'id' => $performance->order->id]) ?>" target="_blank"><?= $performance->order->sn; ?></a>
                                            <?php else: ?>
                                                <?= $performance->order->sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= $performance->administrator_name.'/'.$performance->department_name.'/'.$performance->administrator->company->name; ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($performance->calculated_performance); ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($performance->performance_reward); ?></td>
                                        <td class="text-center"><?= $performance->creator_name; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?=
                            LinkPager::widget([
                                'pagination' => $pagination
                            ]);
                            ?>
                        </div>
                        <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>业绩提成金额更正计算历史</b>：</p>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 304px;">更正时间</th>
                                    <th class="text-center" style="width: 265px;">虚拟订单号</th>
                                    <th class="text-center" style="width: 265px;">子订单号</th>
                                    <th class="text-center" style="width: 265px;">金额名称</th>
                                    <th class="text-center" style="width: 265px;">金额归属对象</th>
                                    <th class="text-center" style="width: 265px;">更正金额</th>
                                    <th class="text-center" style="width: 265px;">提点</th>
                                    <th class="text-center" style="width: 265px;">提成金额</th>
                                    <th class="text-center" style="width: 265px;">更正备注</th>
                                    <th class="text-center" style="width: 224px;">操作人</th>
                                </tr>
                                </thead>
                                <tbody id="cost-record-list">
                                <?php foreach ($correctPerformance as $item): ?>
                                    <tr style="background: none;">
                                        <td class="text-center"><?= Yii::$app->formatter->asDatetime($item->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                        <td class="text-center">
                                            <?php if (Yii::$app->user->can('virtual-order-list/list')): ?>
                                                <a href="<?= Url::to(['virtual-order/order', 'vid' => $item->order->virtual_order_id]) ?>" target="_blank"><?= $item->order->virtualOrder->sn; ?></a>
                                            <?php else: ?>
                                                <?= $item->order->virtualOrder->sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (Yii::$app->user->can('virtual-order-action/detail') || $item->order->hasDetail()): ?>
                                                <a href="<?= Url::to(['order/info', 'id' => $item->order->id]) ?>" target="_blank"><?= $item->order->sn; ?></a>
                                            <?php else: ?>
                                                <?= $item->order->sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= $item->title; ?></td>
                                        <td class="text-center"><?= $item->administrator_name.'/'.$item->department_name.'/'.$item->administrator->company->name; ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($item->calculated_performance); ?></td>
                                        <td class="text-center"><?= floatval($item->reward_proportion).'%'; ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($item->performance_reward); ?></td>
                                        <td class="text-center"><?= $item->remark; ?></td>
                                        <td class="text-center"><?= $item->creator_name; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
