<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use imxiangli\select2\Select2Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $searchModel \backend\models\BillsPersonalSearch*/
/* @var $provider yii\data\ActiveDataProvider */
/* @var $target integer */

$this->title = '记账簿';
$this->params['breadcrumbs'][] = $this->title;
/** @var \common\models\Administrator[] $models */
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
                                'eventSelect' => new JsExpression("
		                               $('#department_id').val('0').trigger('change');
		                                ")
                            ])->label('所属公司');
                            $companyUrl = \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']);
                            echo $form->field($searchModel, 'department_id')->widget(Select2Widget::className(), [
                                'selectedItem' => $searchModel->department ? [$searchModel->department->id => $searchModel->department->name] : [],
                                'width' => '200px',
                                'placeholderId' => '0',
                                'placeholder' => '请选择部门',
                                'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']),
                                'itemsName' => 'department',
                                'eventOpening' => new JsExpression("
		                                var id = $('#company_id').val();
		                                serverUrl = '{$companyUrl}'.replace('__company_id__', id ? id : '-1');
		                    ")
                            ])->label('所属部门');?>
                            <?= $form->field($searchModel, 'keyword', $labelOptions)->textInput(['placeholder'=>'手机号/业务员姓名']) ?>
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
                                    <th class="text-center" style="width: 265px;">账号类型</th>
                                    <th class="text-center" style="width: 265px;">真实姓名/手机号</th>
                                    <th class="text-center" style="width: 265px;">所属公司</th>
                                    <th class="text-center" style="width: 265px;">所属部门</th>
                                    <th class="text-center" style="width: 265px;">账号状态</th>
                                    <th class="text-center" style="width: 265px;">在职状态</th>
                                    <th class="text-center" style="width: 224px;">操作</th>
                                </tr>
                                </thead>
                                <tbody id="cost-record-list">
                                <?php foreach ($models as $model):
                                    $options = [
                                        'id' => false,
                                        'class' => 'change-status-checkbox',
                                        'label' => false,
                                        'data-id' => $model->id,
                                        'readonly' => 'readonly',
                                    ];
                                    ?>
                                    <tr style="background: none;">
                                        <td class="text-center"><?= $model->id; ?></td>
                                        <td class="text-center"><?= $model->getTypeName(); ?></td>
                                        <td class="text-center"><?= $model->name.'/'.$model->phone; ?></td>
                                        <td class="text-center"><?= $model->company ? $model->company->name : '--'; ?></td>
                                        <td class="text-center"><?= $model->department ? $model->department->name : '--'; ?></td>
                                        <td class="text-center">
                                            <label>
                                                <?= Html::activeCheckbox($model, 'status', $options); ?>
                                            </label>
                                        </td>
                                        <td class="text-center"><?= $model->is_dimission ? '离职' : '在职'; ?></td>
                                        <td class="text-center">
                                            <a href="<?= Url::to(['bills-book/detail','id' => $model->id]); ?>" target="_blank">详情</a>
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
<?php
$this->registerJs(<<<JS
    var currentCheckbox = null;
    var statusList = document.querySelectorAll('.change-status-checkbox');
    var statusSwitchery = null;
    for(var i = 0; i < statusList.length; i++)
    {
        statusSwitchery = new Switchery(statusList[i], {"size":"small","className":"switchery"});
        (function (checkbox){
            $(checkbox.element).click(function(){
                currentCheckbox = checkbox;
                return false;
            });
        })(statusSwitchery);
    }
JS
);
?>
