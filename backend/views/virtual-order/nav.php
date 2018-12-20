<?php
/** @var $this \yii\web\View */

use yii\helpers\Url;

/** @var $model \common\models\VirtualOrder */
$uniqueId = Yii::$app->controller->action->uniqueId;
?>
<div class="row">
    <div class="col-lg-12">
        <ul class="nav nav-tabs">
            <li <?php if($uniqueId == 'virtual-order/order'): ?>class="active"<?php endif;?>>
                <a href="<?= Url::to(['virtual-order/order','vid' => $model->id]) ?>">子订单</a>
            </li>
            <?php if(Yii::$app->user->can('virtual-order/expected-cost-list')): ?>
            <li <?php if($uniqueId == 'virtual-order/expected-cost'): ?>class="active"<?php endif;?>>
                <a href="<?= Url::to(['virtual-order/expected-cost','vid' => $model->id]) ?>">预计成本和预计利润</a>
            </li>
            <?php endif; ?>
            <?php if(Yii::$app->user->can('virtual-order/cost-list') || Yii::$app->user->can('virtual-order/score')): ?>
            <li <?php if($uniqueId == 'virtual-order/cost' || $uniqueId == 'virtual-order/score'): ?>class="active"<?php endif;?>>
                <a href="<?= Url::to(['virtual-order/cost','vid' => $model->id]) ?>">实际成本和实际利润</a>
            </li>
            <?php endif; ?>
            <li <?php if($uniqueId == 'virtual-order/record'): ?>class="active"<?php endif;?>>
                <a href="<?= Url::to(['virtual-order/record','vid' => $model->id]) ?>">操作记录</a>
            </li>
            <li <?php if($uniqueId == 'virtual-order/turnover' || $uniqueId == 'virtual-order/performance-turnover'): ?>class="active"<?php endif;?>>
                <a href="<?= Url::to(['virtual-order/turnover','vid' => $model->id]) ?>">订单流水</a>
            </li>
        </ul>
    </div>
</div>