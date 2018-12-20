<?php
/** @var $this \yii\web\View */
/* @var $provider \yii\data\ActiveDataProvider */

use backend\widgets\LinkPager;
use common\utils\Decimal;
use yii\helpers\Url;

/** @var \common\models\PerformanceStatistics[] $models  */
$models = $provider->getModels();
$pagination = $provider->getPagination();
$uniqueId = Yii::$app->controller->action->uniqueId;
?>
<?= $this->render('view',['model' => $model]) ?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="tabs-container">
                <?= $this->render('nav',['model' => $model]) ?>
                <!--子预计利润流水列表-->
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <div style="overflow: hidden;padding-bottom: 20px;">
                            <div class="payment-button">
                                <div class="clearfloat">
                                    <a href="<?= Url::to(['virtual-order/turnover','vid' => $model->id]) ?>" class="<?php if($uniqueId == 'virtual-order/turnover'): ?>payment-button-active<?php endif; ?>">预计利润流水</a>
                                    <a href="<?= Url::to(['virtual-order/performance-turnover','vid' => $model->id]) ?>" class="<?php if($uniqueId == 'virtual-order/performance-turnover'): ?>payment-button-active<?php endif; ?>">提成计算流水</a>
                                </div>
                            </div>
                        </div>
                        <table class="footable table table-striped">
                            <thead>
                            <tr style="border-top: 1px solid #e7eaec;">
                                <th>计算时间</th>
                                <th>计算类型</th>
                                <th>提点类型</th>
                                <th>业务员/部门/公司</th>
                                <th>提点</th>
                                <th>子订单号</th>
                                <th>已计算实际利润金额</th>
                                <th>提成金额</th>
                                <th>操作者</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if($models): ?>
                                <?php foreach ($models as $model): ?>
                                    <tr>
                                        <td><?= date('Y-m-d H:i:s',$model->created_at); ?></td>
                                        <td><?= $model->getTypeName(); ?></td>
                                        <td><?= $model->getAlgorithmName(); ?></td>
                                        <td><?= $model->administrator_name.'/'.$model->department_name.($model->administrator->company ? '/'.$model->administrator->company->name : ''); ?></td>
                                        <td><?= floatval($model->reward_proportion).'%'; ?></td>
                                        <td>
                                            <?php if (Yii::$app->user->can('virtual-order-action/detail') || $model->order->hasDetail()): ?>
                                                <a href="<?= Url::to(['order/info', 'id' => $model->order->id]) ?>" target="_blank"><?= $model->order->sn; ?></a>
                                            <?php else: ?>
                                                <?= $model->order->sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= Decimal::formatYenCurrentNoWrap($model->calculated_performance); ?></td>
                                        <td><?= Decimal::formatYenCurrentNoWrap($model->performance_reward); ?></td>
                                        <td><?= $model->creator_name; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                         <?php if($total): ?>
                            <?php foreach ($total as $item): ?>
                                <?= $item['administrator_name']; ?>:
                                <?= Decimal::formatYenCurrentNoWrap($item['price']); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?=
                        LinkPager::widget([
                            'pagination' => $pagination
                        ]);
                        ?>
                    </div>
                </div>
                <!--子预计利润流水列表-->
            </div>
        </div>
    </div>
</div>
