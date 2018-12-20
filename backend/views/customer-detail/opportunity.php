<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/14
 * Time: 下午3:32
 */

use backend\widgets\LinkPager;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use zhuravljov\yii\widgets\DateTimePicker;

/** @var \yii\web\View $this */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var \common\models\CrmOpportunity[] $models */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
$status = Yii::$app->request->get('status');
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
                                    <li <?php if ($status == 'deal'): ?>  class="active" <?php endif; ?>>
                                        <a href="<?= \yii\helpers\Url::to(['customer-detail/opportunity', 'customer_id'=>$customer->id, 'status' => 'deal']) ?>">已成交商机</a>
                                    </li>
                                    <li <?php if ($status == ''): ?>  class="active" <?php endif; ?>>
                                        <a href="<?= \yii\helpers\Url::to(['customer-detail/opportunity', 'customer_id'=>$customer->id]) ?>">未成交商机</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active">
                                        <div class="panel-body" style="position:relative">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>商机ID</th>
                                                    <th>商机信息</th>
                                                    <th>商品信息</th>
                                                    <th>跟进状态</th>
                                                    <th>商机状态</th>
                                                    <th>商机金额</th>
                                                    <th>最后跟进时间</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($models as $model): ?>
                                                    <tr data-id="<?= $model->id; ?>">
                                                        <td><?= $model->id; ?></td>
                                                        <td>
、                                                           <p><?= $model->name; ?></p>
                                                            <p><?= Yii::$app->formatter->asDatetime($model->created_at) ?></p>
                                                        </td>
                                                        <td>
                                                            <?php if (isset($model->opportunityProducts[0])):?>
                                                            <p><?= $model->opportunityProducts[0]->product_name ?></p>
                                                            <p><?= $model->opportunityProducts[0]->province_name ?>-<?= $model->opportunityProducts[0]->city_name ?>-<?= $model->opportunityProducts[0]->district_name ?></p>
                                                            <?php endif;?>
                                                        </td>
                                                        <td>
                                                            <?php if ($model->isPublic()):?>
                                                                <p>待提取</p>
                                                            <?php else:?>
                                                                <p><?= $model->is_receive ? '已确认' : '待确认'; ?></p>
                                                                <p><?= $model->administrator_name; ?></p>
                                                            <?php endif;?>
                                                        </td>
                                                        <td>
                                                            <?= $model->getStatusName(); ?>
                                                        </td>
                                                        <td>
                                                            <?= Yii::$app->formatter->asCurrency($model->total_amount); ?>
                                                        </td>
                                                        <td>
                                                            <p><?= $model->last_record ? Yii::$app->formatter->asDatetime($model->last_record) : '--'; ?></p>
                                                            <p>下次跟进时间：<?= $model->next_follow_time ? Yii::$app->formatter->asDatetime($model->next_follow_time) : '--'; ?></p>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                <tr>
                                                    <td colspan="11">
                                                        <?= LinkPager::widget(['pagination' => $pagination]); ?>
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
