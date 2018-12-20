<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use common\models\BusinessSubject;
use common\models\Industry;
use imxiangli\select2\Select2Widget;
use yii\helpers\Url;
use yii\web\JsExpression;
use zhuravljov\yii\widgets\DateTimePicker;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var \backend\models\BusinessSubjectSearch $searchModel */

/** @var BusinessSubject[] $models */
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
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'action' => ['subject'],
                        'layout' => 'inline',
                        'method' => 'get',
                    ]); ?>

                    <?= $form->field($searchModel, 'industry_id', $labelOptions)->widget(Select2Widget::className(), [
                        'selectedItem' => Industry::getIndustry(),
                        'placeholderId' => '0',
                        'placeholder' => '请选择类型',
                        'options' => ['class' => 'form-control'],
                        'static' => true,
                    ]) ?>

                    <?= $form->field($searchModel, 'province_id', $labelOptions)->widget(Select2Widget::className(), [
                        'serverUrl' => Url::to(['region/ajax-provinces']),
                        'itemsName' => 'provinces',
                        'selectedItem' => $searchModel->province ? [$searchModel->province->id => $searchModel->province->name] : [],
                        'options' => ['class' => 'form-control', 'prompt' => '请选择省份'],
                        'placeholderId' => '0',
                        'placeholder' => '请选择省份',
                        'eventSelect' => new JsExpression("
                                    $('#city_id').val('0').trigger('change');
                                    $('#district_id').val('0').trigger('change');
                                ")
                    ]); ?>

                    <?= $form->field($searchModel, 'city_id', $labelOptions)->widget(Select2Widget::className(), [
                        'serverUrl' => Url::to(['region/ajax-cities', 'province_id' => '__province_id__']),
                        'itemsName' => 'cities',
                        'selectedItem' => $searchModel->city ? [$searchModel->city->id => $searchModel->city->name] : [],
                        'options' => ['class' => 'form-control', 'prompt' => '请选择城市'],
                        'placeholderId' => '0',
                        'placeholder' => '请选择城市',
                        'eventSelect' => new JsExpression("
                                    $('#district_id').val('0').trigger('change');
                                "),
                        'eventOpening' => new JsExpression("
                                    var id = $('#province_id').val();
                                    serverUrl = '{$cityUrl}'.replace('__province_id__', id ? id : '-1');
                                ")
                    ]); ?>

                    <?= $form->field($searchModel, 'district_id', $labelOptions)->widget(Select2Widget::className(), [
                        'serverUrl' => Url::to(['region/ajax-districts', 'city_id' => '__city_id__']),
                        'selectedItem' => $searchModel->district ? [$searchModel->district->id => $searchModel->district->name] : [],
                        'options' => ['class' => 'form-control', 'prompt' => '请选择地区'],
                        'placeholderId' => '0',
                        'placeholder' => '请选择地区',
                        'itemsName' => 'districts',
                        'eventOpening' => new JsExpression("
                                    var id = $('#city_id').val();
                                    serverUrl = '{$districtUrl}'.replace('__city_id__', id ? id : '-1');
                                ")
                    ]); ?>
                    <br><br>
                    <b>成立时间</b>
                    <?= $form->field($searchModel, 'starting_time')->widget(DateTimePicker::className(), [
                        'clientOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'language' => 'zh-CN',
                            'autoclose' => true,
                            'minView' => 'month',
                        ],
                        'clientEvents' => [],
                    ]) ?>
                    <?= $form->field($searchModel, 'end_time')->widget(DateTimePicker::className(), [
                        'clientOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'language' => 'zh-CN',
                            'autoclose' => true,
                            'minView' => 'month',
                        ],
                        'clientEvents' => [],
                    ]) ?>
                    <br><br>
                    <?= $form->field($searchModel, 'tax_type', $labelOptions)->widget(Select2Widget::className(), [
                        'selectedItem' => BusinessSubject::getTaxType(),
                        'placeholderId' => '0',
                        'placeholder' => '请选择类型',
                        'options' => ['class' => 'form-control'],
                        'static' => true,
                    ]) ?>

                    <?= $form->field($searchModel, 'type', $labelOptions)->dropDownList(\backend\models\BusinessSubjectSearch::getTypes());?>
                    <?= $form->field($searchModel, 'keyword', $labelOptions)->textInput(['placeholder'=>'请输入关键词']) ?>
                    <button type="submit" class="btn btn-primary">搜索</button>
                    <?php \yii\bootstrap\ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <div class="row page-border">
            <div class="margin-auto col-md-12">
                <?= LinkPager::widget(['pagination' => $pagination]); ?>
            </div>
        </div>
        <div class="ibox-content" style="border-width: 1px;padding-bottom: 0;">
            <table class="table no-margins">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>企业名称</th>
                    <th>注册地区</th>
                    <th>税务类型</th>
                    <th>行业类型</th>
                    <th>成立日期</th>
                    <th>注册资本</th>
                    <th>公司类型</th>
                    <th>关联订单</th>
                    <th>关联商机</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($models as $key => $model): ?>
                    <tr>
                        <td class="col-md-1"><?= $model->id; ?></td>
                        <td class="col-md-2">
                            <a <?php if(Yii::$app->user->can('business-subject/detail')): ?>href="<?= Url::to(['business-subject/information','id'=>$model->id]) ?>"<?php endif;?> target="_blank">
                                <?= $model->company_name ?>
                            </a>
                        </td>
                        <td class="col-md-1"><?= $model->province_name.$model->city_name.$model->district_name; ?></td>
                        <td class="col-md-1"><?= $model->getTxtName(); ?></td>
                        <td class="col-md-1"><?= $model->industry_name ?></td>
                        <td class="col-md-1"><?= empty($model->operating_period_begin)?'--':Yii::$app->formatter->asDate($model->operating_period_begin); ?></td>
                        <td class="col-md-1"><?= $model->registered_capital.'万元' ?></td>
                        <td class="col-md-2"><?= $model->enterprise_type ?></td>
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