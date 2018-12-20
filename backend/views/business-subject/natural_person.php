<?php

/* @var $this yii\web\View */
use backend\models\BusinessSubjectSearch;
use backend\widgets\LinkPager;
use common\models\BusinessSubject;
use common\models\Industry;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use zhuravljov\yii\widgets\DateTimePicker;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var BusinessSubjectSearch $searchModel */

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$imageStorage = Yii::$app->get('imageStorage');

?>
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <ul class="nav nav-tabs">
                    <?php if (Yii::$app->user->can('business-subject/list')): ?>
                        <li<?php if (Yii::$app->controller->action->id == 'subject'): ?> class="active"<?php endif; ?>>
                            <a href="<?= Url::to(['business-subject/subject']) ?>">企业主体</a>
                        </li>
                    <?php endif; ?>

                    <?php if (Yii::$app->user->can('business-subject/list')): ?>
                        <li<?php if (Yii::$app->controller->action->id == 'natural-person'): ?> class="active"<?php endif; ?>>
                            <a href="<?= Url::to(['business-subject/natural-person']) ?>">自然人主体</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="tab-content">
                    <div class="page-select2-area panel-body " style="border-top:none;">
                        <?php
                        $labelOptions = ['labelOptions' => ['class' => false]];
                        $categoryUrl = Url::to(['product-category/ajax-list', 'parent_id' => '__parent_id__']);
                        $cityUrl = Url::to(['region/ajax-cities', 'province_id' => '__province_id__']);
                        $districtUrl = Url::to(['region/ajax-districts', 'city_id' => '__city_id__']);
                        $form = ActiveForm::begin([
                            'action' => ['natural-person'],
                            'layout' => 'inline',
                            'method' => 'get',
                        ]); ?>

                        <?= $form->field($searchModel, 'identity_type', $labelOptions)->dropDownList(BusinessSubjectSearch::getIdentityTypes());?>
                        <?= $form->field($searchModel, 'keyword', $labelOptions)->textInput(['placeholder'=>'请输入关键词']) ?>
                        <button type="submit" class="btn btn-primary">搜索</button>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
                <div class="row page-border">
                    <div class="margin-auto col-md-12">
                        <?= LinkPager::widget(['pagination' => $pagination]); ?>
                    </div>
                </div>
                <div class="ibox-content" style="border-width: 1px;padding-bottom: 0;">
                    <table class="table row no-margins">
                        <thead>
                        <tr>
                            <th class="col-md-1 no-borders">ID</th>
                            <th class="col-md-2 no-borders">姓名</th>
                            <th class="col-md-2 no-borders">身份证</th>
                            <th class="col-md-5 no-borders">户籍地址</th>
                            <th class="col-md-1 no-borders">关联订单</th>
                            <th class="col-md-1 no-borders">关联商机</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        /**@var BusinessSubject[] $models ***/
                        foreach ($models as $model): ?>
                            <tr>
                                <td class="col-md-1"><?= $model->id; ?></td>
                                <td class="col-md-2">
                                    <a <?php if(Yii::$app->user->can('business-subject/detail')): ?>href="<?= Url::to(['business-subject/information','id'=>$model->id]) ?>"<?php endif;?> target="_blank">
                                        <?= $model->region ?>
                                    </a>
                                </td>
                                <td class="col-md-2"><?= $model->name; ?></td>
                                <td class="col-md-5"><?= $model->scope; ?></td>
                                <td class="col-md-1">
                                    <a <?php if(Yii::$app->user->can('business-subject/list')): ?>href="<?= \yii\helpers\Url::to(['business-subject/order', 'status' => 'paid', 'id' => $model->id])?>"<?php endif; ?> target="_blank">
                                        <?= '已付:'.$model->getAlreadyPayCount() ?>
                                    </a><br>
                                    <a <?php if(Yii::$app->user->can('business-subject/list')): ?>href="<?= \yii\helpers\Url::to(['business-subject/order', 'status' => 'pending-pay', 'id' => $model->id])?>"<?php endif; ?> target="_blank">
                                        <?= '未付:'.$model->getUnpaidCount() ?>
                                    </a>
                                </td>
                                <td class="col-md-1">
                                    <a <?php if(Yii::$app->user->can('business-subject/list')): ?>href="<?= \yii\helpers\Url::to(['business-subject/opportunity', 'id' => $model->id, 'status' => 'deal'])?>"<?php endif; ?> target="_blank">
                                        <?= '已成交:'.$model->getOpportunityDealCounts() ?><br>
                                    </a>
                                    <a <?php if(Yii::$app->user->can('business-subject/list')): ?>href="<?= \yii\helpers\Url::to(['business-subject/opportunity', 'id' => $model->id])?>"<?php endif; ?> target="_blank">
                                        <?= '未成交:'.$model->getOpportunityNotDealCounts() ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="row page-border m-b-lg">
                    <div class="margin-auto col-md-12">
                        <?= LinkPager::widget(['pagination' => $pagination]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
