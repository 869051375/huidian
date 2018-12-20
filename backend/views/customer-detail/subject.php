<?php
/* @var $this yii\web\View */
use backend\models\BusinessSubjectForm;
use backend\models\CustomerLogForm;
use common\models\BusinessSubject;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

/* @var BusinessSubject[] $model */
/* @var \common\models\CrmCustomer $customer */
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
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
                                    <div class="tabs-container" style="position:relative">
                                        <ul class="nav nav-tabs">
                                            <li class="active"><a data-toggle="tab" href="#tab-3">企业主体</a></li>
                                            <li class=""><a data-toggle="tab" href="#tab-4">自然人主体</a></li>

                                        </ul>
                                        <div style="position:absolute; top:-2px;right:0;">
                                        </div>
                                        <div class="tab-content">
                                            <div id="tab-3" class="tab-pane active">
                                                <div class="panel-body">
                                                    <table class="table row">
                                                        <thead>
                                                        <tr>
                                                            <th class="col-md-2">企业名称</th>
                                                            <th class="col-md-3">注册地址</th>
                                                            <th class="col-md-3">企业类型</th>
                                                            <th class="col-md-1">行业类型</th>
                                                            <th class="col-md-1">税务类型</th>
                                                            <th class="col-md-1">关联订单</th>
                                                            <th class="col-md-1">关联商机</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php if(BusinessSubject::getSubjectCount($model,BusinessSubject::SUBJECT_TYPE_DISABLED)): ?>
                                                        <?php foreach($model as $business): ?>
                                                        <?php if(empty($business->subject_type)): ?>
                                                            <tr>
                                                                <td class="col-md-2 " style="width: 100px;word-wrap:break-word;">
                                                                    <a <?php if(Yii::$app->user->can('business-subject/detail')): ?>href="<?= Url::to(['business-subject/information','id'=>$business->id]) ?>"<?php endif;?> target="_blank">
                                                                        <?= $business->company_name ?>
                                                                    </a>
                                                                </td>
                                                                <td class="col-md-3" style="width: 100px;word-wrap:break-word;"><?= $business->province_name ?><?= $business->city_name ?><?= $business->district_name ?><?= $business->address ?></td>
                                                                <td class="col-md-3"><?= $business->enterprise_type ?></td>
                                                                <td class="col-md-1">
                                                                    <?php if($business->industry_id&&$business->industry_id!=999): ?>
                                                                        <?= $business->industry_name ?>
                                                                    <?php elseif($business->industry_id==999): ?>
                                                                        其他行业（<?= $business->industry_name ?>）
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="col-md-1"><?= $business->getTxtName(); ?></td>
                                                                <td class="col-md-1">
                                                                    <a <?php if(Yii::$app->user->can('business-subject/list')): ?>href="<?= \yii\helpers\Url::to(['business-subject/order', 'status' => 'paid', 'id' => $business->id])?>"<?php endif; ?> target="_blank">
                                                                    <?= '已付:'.$business->getAlreadyPayCount() ?>
                                                                    </a><br>
                                                                    <a <?php if(Yii::$app->user->can('business-subject/list')): ?>href="<?= \yii\helpers\Url::to(['business-subject/order', 'status' => 'pending-pay', 'id' => $business->id])?>"<?php endif; ?> target="_blank">
                                                                    <?= '未付:'.$business->getUnpaidCount() ?>
                                                                    </a>
                                                                </td>
                                                                <td class="col-md-1">
                                                                    <a <?php if(Yii::$app->user->can('business-subject/list')): ?>href="<?= \yii\helpers\Url::to(['business-subject/opportunity', 'id' => $business->id, 'status' => 'deal'])?>"<?php endif; ?> target="_blank">
                                                                    <?= '已成交:'.$business->getOpportunityDealCounts() ?><br>
                                                                    </a>
                                                                    <a <?php if(Yii::$app->user->can('business-subject/list')): ?>href="<?= \yii\helpers\Url::to(['business-subject/opportunity', 'id' => $business->id])?>"<?php endif; ?> target="_blank">
                                                                    <?= '未成交:'.$business->getOpportunityNotDealCounts() ?>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
                                                        <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="7" class="text-center">暂时无数据</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div id="tab-4" class="tab-pane">
                                                <div class="panel-body">
                                                    <table class="table">
                                                        <thead>
                                                        <tr>
                                                            <th>姓名</th>
                                                            <th>身份证</th>
                                                            <th>户籍地址</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php if(BusinessSubject::getSubjectCount($model,BusinessSubject::SUBJECT_TYPE_ACTIVE)): ?>
                                                        <?php foreach($model as $business): ?>
                                                        <?php if($business->subject_type): ?>
                                                            <tr>
                                                                <td>
                                                                    <a <?php if(Yii::$app->user->can('business-subject/detail')): ?>href="<?= Url::to(['business-subject/information','id'=>$business->id]) ?>"<?php endif;?> target="_blank">
                                                                        <?= $business->region ?>
                                                                    </a>
                                                                </td>
                                                                <td><?= $business->name ?></td>
                                                                <td><?= $business->scope ?></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                        <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="7" class="text-center">暂时无数据</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                        </tbody>
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