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
/** @var \common\models\BusinessSubject $subject */
/** @var \common\models\CrmOpportunity[] $models */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
$status = Yii::$app->request->get('status');
?>

<div class="wrapper wrapper-content animated fadeIn">
    <?= $this->render('info', ['subject' => $subject]) ?>
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <?= $this->render('nav-tabs', ['subject' => $subject]) ?>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none;">

                        <div class="col-lg-12">
                            <div class="tabs-container">
                                <ul class="nav nav-tabs">
                                    <li <?php if ($status == 'deal'): ?>  class="active" <?php endif; ?>>
                                        <a href="<?= \yii\helpers\Url::to(['business-subject/opportunity', 'id' => $subject->id, 'status' => 'deal']) ?>">已成交商机</a>
                                    </li>
                                    <li <?php if ($status == ''): ?>  class="active" <?php endif; ?>>
                                        <a href="<?= \yii\helpers\Url::to(['business-subject/opportunity', 'id' => $subject->id]) ?>">未成交商机</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active">
                                        <div class="panel-body" style="padding-bottom: 0;">
                                        	<?php if (Yii::$app->user->can('opportunity/*')): ?>
                                            <a class="btn btn-primary" style="position:absolute; top:-42px; right: 0;" href="<?= Url::to(['opportunity/create','customer_id'=>$subject->customer->id]) ?>">新增商机</a>
                                            <?php endif; ?>
                                            <table class="table" style="border: none;margin: 0;">
                                                <thead>
                                                <tr style="border-bottom: 1px solid #e7eaec;">
                                                    <th style="background: none;border: none;">商机ID</th>
                                                    <th style="background: none;border: none;">商机信息</th>
                                                    <th style="background: none;border: none;">商品信息</th>
                                                    <th style="background: none;border: none;">跟进状态</th>
                                                    <th style="background: none;border: none;">商机状态</th>
                                                    <th style="background: none;border: none;">商机金额</th>
                                                    <th style="background: none;border: none;">最后跟进时间</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if(!empty($models)): ?>
                                                    <?php foreach ($models as $model): ?>
	                                                    <tr data-id="<?= $model->id; ?>" style="border-bottom: 1px solid #e7eaec;">
	                                                        <td style="border: none;vertical-align: middle;"><?= $model->id; ?></td>
	                                                        <td style="border: none;vertical-align: middle;">
	                                                            <a <?php if (Yii::$app->user->can('opportunity/*')): ?>
	                                                                    href="<?= Url::to(['opportunity/view', 'id' => $model->id])?>" target="_blank" <?php endif; ?>>
	                                                                <p><?= $model->name; ?></p>
	                                                                <p><?= Yii::$app->formatter->asDatetime($model->created_at) ?></p>
	                                                            </a>
	                                                        </td>
	                                                        <td style="border: none;vertical-align: middle;">
                                                                <?php if (isset($model->opportunityProducts[0])):?>
	                                                            <p><?= $model->opportunityProducts[0]->product_name ?></p>
	                                                            <p><?= $model->opportunityProducts[0]->province_name ?>-<?= $model->opportunityProducts[0]->city_name ?>-<?= $model->opportunityProducts[0]->district_name ?></p>
                                                                <?php endif;?>
	                                                        </td>
	                                                        <td style="border: none;vertical-align: middle;">
	                                                            <p><?= $model->is_receive ? '已确认' : '待确认'; ?></p>
	                                                            <p><?= $model->administrator_name; ?></p>
	                                                        </td>
	                                                        <td style="border: none;vertical-align: middle;">
	                                                            <?= $model->getStatusName(); ?>
	                                                        </td>
	                                                        <td style="border: none;vertical-align: middle;">
	                                                            <?= Yii::$app->formatter->asCurrency($model->total_amount); ?>
	                                                        </td>
	                                                        <td style="border: none;vertical-align: middle;">
	                                                            <p><?= $model->last_record ? Yii::$app->formatter->asDatetime($model->last_record) : '--'; ?></p>
	                                                            <p>下次跟进时间：<?= $model->next_follow_time ? Yii::$app->formatter->asDatetime($model->next_follow_time) : '--'; ?></p>
	                                                        </td>
	                                                    </tr>
	                                                <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center" style="border: none;border-bottom: 1px solid #e7eaec;">暂无数据</td>
                                                    </tr>
                                                <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                <tr>
                                                    <td colspan="11" style="border: none;padding: 0;">
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
