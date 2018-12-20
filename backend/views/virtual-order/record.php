<?php
/** @var $this \yii\web\View */
/* @var $provider \yii\data\ActiveDataProvider */

use backend\widgets\LinkPager;
use yii\helpers\Url;

/** @var \common\models\OrderRecord[] $models  */
$models = $provider->getModels();
$pagination = $provider->getPagination();
?>
<?= $this->render('view',['model' => $model]) ?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="tabs-container">
                <?= $this->render('nav',['model' => $model]) ?>
                <!--子订单记录列表-->
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <table class="footable table table-striped">
                            <thead>
                            <tr style="border-top: 1px solid #e7eaec;">
                                <th>操作时间</th>
                                <th>子订单号</th>
                                <th>操作状态</th>
                                <th>操作内容</th>
                                <th>操作者</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if($models): ?>
                                <?php foreach ($models as $model): ?>
                                    <tr>
                                        <td><?= date('Y-m-d H:i:s',$model->created_at); ?></td>
                                        <td>
                                            <?php if (Yii::$app->user->can('virtual-order-action/detail') || $model->order->hasDetail()): ?>
                                                <a href="<?= Url::to(['order/info', 'id' => $model->order->id]) ?>" target="_blank"><?= $model->order->sn; ?></a>
                                            <?php else: ?>
                                                <?= $model->order->sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $model->title; ?></td>
                                        <td><?= $model->remark; ?></td>
                                        <td><?= $model->creator_name; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        <?=
                        LinkPager::widget([
                            'pagination' => $pagination
                        ]);
                        ?>
                    </div>
                </div>
                <!--子订单记录列表-->
            </div>
        </div>
    </div>
</div>
