<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use imxiangli\select2\Select2Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $searchModel \backend\models\BillsDepartmentSearch*/
/* @var $provider yii\data\ActiveDataProvider */
/* @var $target integer */

$this->title = '记账簿';
$this->params['breadcrumbs'][] = $this->title;
/** @var \common\models\CrmDepartment[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
\toxor88\switchery\SwitcheryAsset::register($this);
$uniqueId = Yii::$app->controller->action->uniqueId;
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
?>
<div class="row">
    <div class="col-xs-12">
        <div class="tabs-container">
                <ul class="nav nav-tabs">
                    <li <?php if($uniqueId == 'bills-book/index'): ?>class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['bills-book/index']) ?>">个人记账簿</a>
                    </li>
                    <li <?php if($uniqueId == 'bills-book/department'): ?>class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['bills-book/department']) ?>">部门记账簿</a>
                    </li>
                </ul>
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel-body">
                        <div class="ibox-box">
                            <?php
                            $labelOptions = ['labelOptions' => ['class' => false]];
                            $form = \yii\bootstrap\ActiveForm::begin([
                                'action' => '/bills-book/' . Yii::$app->controller->action->id,
                                'layout' => 'inline',
                                'method' => 'get',
                            ]); ?>
                            <b>所属公司</b>
                            <?= $form->field($searchModel, 'company_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                                'selectedItem' => $searchModel->company ? [$searchModel->company->id => $searchModel->company->name] : [],
                                'serverUrl' => \yii\helpers\Url::to(['company/ajax-list', 'company_id' => $administrator->company_id ? $administrator->company_id : null]),
                                'itemsName' => 'company',
                                'placeholderId' => '0',
                                'width' => '200px',
                                'placeholder' => '请选择公司',
                                'searchKeywordName' => 'keyword',
                            ])->label('所属公司');
                            ?>
                            <b>部门状态</b>
                            <?= $form->field($searchModel,'status')->dropDownList([1 => '正常',2 => '已删除']) ?>
                            <?= $form->field($searchModel, 'keyword', $labelOptions)->textInput(['placeholder'=>'部门名称']) ?>
                            <button type="submit" class="btn btn-primary">搜索</button>
                            <?php \yii\bootstrap\ActiveForm::end(); ?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="margin-auto">
                                <?=
                                LinkPager::widget([
                                    'pagination' => $pagination
                                ]);
                                ?>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 304px;">编号ID</th>
                                    <th class="text-center" style="width: 265px;">部门名称</th>
                                    <th class="text-center" style="width: 265px;">所属公司</th>
                                    <th class="text-center" style="width: 265px;">部门状态</th>
                                    <th class="text-center" style="width: 224px;">操作</th>
                                </tr>
                                </thead>
                                <tbody id="cost-record-list">
                                <?php foreach ($models as $model): ?>
                                    <tr style="background: none;">
                                        <td class="text-center"><?= $model->id; ?></td>
                                        <td class="text-center"><?= $model->name; ?></td>
                                        <td class="text-center"><?= $model->company->name; ?></td>
                                        <td class="text-center"><?= $model->status ? '正常' : '已删除'; ?></td>
                                        <td class="text-center">
                                            <a href="<?= Url::to(['bills-book/department-detail','did' => $model->id]); ?>" target="_blank">详情</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="margin-auto">
                                <?=
                                LinkPager::widget([
                                    'pagination' => $pagination
                                ]);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
