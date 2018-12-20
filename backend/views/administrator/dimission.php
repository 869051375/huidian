<?php

use backend\assets\CompanyAsset;
use backend\widgets\LinkPager;
use common\models\Administrator;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \backend\models\CompanySearch $searchModel */
/** @var Administrator $administrator */
$administrator = Yii::$app->user->identity;
$this->title = '离职人员列表';
$this->params['breadcrumbs'][] = $this->title;
/** @var Administrator[] $models */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$actionUniqueId = Yii::$app->controller->action->uniqueId;
$searchUrl = 'administrator/dimission';
$params = Yii::$app->request->queryParams;
\toxor88\switchery\SwitcheryAsset::register($this);
CompanyAsset::register($this);
?>
<!--todo 待前端样式优化-->
<style type="text/css">
    .personnel-list ul li { float:left; margin-left:20px; list-style-type:none;}
</style>
<div class="row">
    <div class="col-xs-12">
        <div class="ibox">
        	<div class="col-xs-12" style="background:#fff;">
        		<?= LinkPager::widget(['pagination' => $pagination]);?>
        	</div>
            <div class="ibox-content row" style="padding:20px 20px 0 20px;margin: 0;">
                <div class="col-xs-9 row" style="padding: 0;margin: 0;">
                    <table class="table table-bordered row" style="padding: 0;margin: 0;">
                        <thead>
	                        <tr>
	                            <th class="text-center">ID</th>
	                            <th class="text-center">账号类别</th>
	                            <th class="text-center">真实姓名/手机号</th>
	                            <th class="text-center">邮箱</th>
	                            <th class="text-center">创建日期</th>
	                            <th class="text-center">所属公司/部门</th>
	                            <th class="text-center">职位</th>
	                            <th class="text-center">最后登录时间</th>
	                            <th class="text-center">操作</th>
	                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($models)):?>
                            <?php /** @var Administrator $model */
                            foreach ($models as $model):
                                ?>
                                <tr>
                                    <td class="text-center" style="vertical-align: middle;"><?= $model->id;?></td>
                                    <td class="text-center" style="vertical-align: middle;"><?= $model->getTypeName();?></td>
                                    <td class="text-center" style="vertical-align: middle;"><?= $model->name;?><br><?= $model->phone;?></td>
                                    <td class="text-center" style="vertical-align: middle;"><?= $model->email;?></td>
                                    <td class="text-center" style="vertical-align: middle;"><?= Yii::$app->formatter->asDatetime($model->created_at);?></td>
                                    <td class="text-center" style="vertical-align: middle;">
                                        <?php if ($model->company && $model->department):?>
                                            <?= $model->company ? $model->company->name :'';?>/<?= $model->department ? $model->department->name : '';?>
                                        <?php endif;?>
                                    </td>
                                    <td class="text-center" style="vertical-align: middle;"><?= $model->title;?></td>
                                    <td class="text-center" style="vertical-align: middle;">
                                        <?= $model->administratorLog ? Yii::$app->formatter->asDatetime($model->administratorLog->created_at) :''?>
                                    </td>
                                    <td class="text-center" style="vertical-align: middle;">
                                        <?php if(Yii::$app->user->can('administrator/ajax-hire')): ?>
                                        <a class="btn btn-warning hire-btn" data-id="<?= $model->id; ?>" data-name="<?= $model->name; ?>"
                                           data-target="#recruitment-modal" data-toggle="modal">返聘</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                        <?php endif;?>
                        </tbody>
                    </table>
                </div>
                <div class="col-xs-3 row" style="padding: 0;margin: 0;">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content"style="padding: 8px;border-right: 1px solid #e7e7e7;background: #f5f5f6;">
                            <p style="text-align:center;margin: 0;">组织机构</p>
                        </div>
                        <div class="ibox-content">
                            <div id="jstree1">
                                <ul>
                                    <?php if ($administrator):?>
                                        <?php /** @var \common\models\Company $companies */
                                        if ($companies):?>
                                            <?php /** @var \common\models\Company $company */
                                            foreach ($companies as $company):
                                                $departments = $company->department;
                                                ?>
                                                <li class="<?php if (isset($params['company_id']) && $params['company_id'] == $company->id):?>jstree-open<?php endif;?>">
                                            <span><a class="<?php if (isset($params['company_id']) && $params['company_id'] == $company->id && isset($params['department_id']) && $params['department_id'] == ''):?> btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                                    'role_type'=>isset($params['role_type']) ? $params['role_type'] : '',
                                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                                    'company_id' =>$company->id,
                                                    'department_id' =>'',
                                                ])?>" onclick="javascript:window.location.href='<?= Url::to([$searchUrl,
                                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                                    'role_type'=>isset($params['role_type']) ? $params['role_type'] : '',
                                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                                    'company_id' =>$company->id,
                                                    'department_id' =>'',
                                                ])?>';"><?= $company->name;?></a></span>
                                                    <ul>
                                                        <!--一级部门-->
                                                        <?php /** @var \common\models\CrmDepartment $departments */
                                                        if ($departments):?>
                                                            <?php /** @var \common\models\CrmDepartment $department */
                                                            $children = [];
                                                            foreach ($departments as $department):
                                                                if ($department->parent_id == 0) {
                                                                    $children = $department->children;
                                                                }
                                                                ?>
                                                                <?php if ($department->parent_id == 0):?>
                                                                <li class="<?php if (isset($params['department_id']) && $params['department_id'] == $department->id || $department->parent_id == 0):?>jstree-open<?php endif;?>">
                                                            <span><a class="<?php if (isset($params['department_id']) && $params['department_id'] == $department->id):?> btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                                                    'role_type'=>isset($params['role_type']) ? $params['role_type'] : '',
                                                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                                                    'company_id' => $company->id,
                                                                    'department_id' =>$department->id,
                                                                ])?>" onclick="javascript:window.location.href='<?= Url::to([$searchUrl,
                                                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                                                    'role_type'=>isset($params['role_type']) ? $params['role_type'] : '',
                                                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                                                    'company_id' => $company->id,
                                                                    'department_id' =>$department->id,
                                                                ])?>';"><?= $department->name;?></a></span>
                                                                    <ul>
                                                                        <!--二级部门-->
                                                                        <?php if ($children):?>
                                                                            <?php /** @var \common\models\CrmDepartment $child */
                                                                            foreach ($children as $child):
                                                                                if ($child->parent_id == $department->id)
                                                                                {
                                                                                    $grandsons = $child->children;
                                                                                }
                                                                                ?>
                                                                                <?php if ($child->parent_id == $department->id):?>
                                                                                <!--                                                                        <li class="jstree-open" data-jstree='"type":"css"}'>-->
                                                                                <li class="<?php if (isset($params['department_id']) && $params['department_id'] == $child->id || $child->parent_id == $department->id):?>jstree-open<?php endif;?>">
                                                                            <span><a class="<?php if (isset($params['department_id']) && $params['department_id'] == $child->id):?>btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                                                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                                                                    'role_type'=>isset($params['role_type']) ? $params['role_type'] : '',
                                                                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                                                                    'company_id' => $company->id,
                                                                                    'department_id' =>$child->id,
                                                                                ])?>" onclick="javascript:window.location.href='<?= Url::to([$searchUrl,
                                                                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                                                                    'role_type'=>isset($params['role_type']) ? $params['role_type'] : '',
                                                                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                                                                    'company_id' => $company->id,
                                                                                    'department_id' =>$child->id,
                                                                                ])?>';"><?= $child->name;?></a></span>
                                                                                    <ul>
                                                                                        <?php /** @var \common\models\CrmDepartment $grandsons */
                                                                                        /** @var \common\models\CrmDepartment $grandson */
                                                                                        if ($grandsons):?>
                                                                                            <?php foreach ($grandsons as $grandson):?>
                                                                                                <?php if ($grandson->parent_id == $child->id):?>
                                                                                                    <!--                                                                                            <li class="jstree-open" data-jstree='"type":"css"}'>-->
                                                                                                    <li class="<?php if (isset($params['department_id']) && $params['department_id'] == $grandson->id):?>jstree-open<?php endif;?>">
                                                                                                <span><a class="<?php if (isset($params['department_id']) && $params['department_id'] == $grandson->id):?>btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                                                                                        'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                                                                                        'role_type'=>isset($params['role_type']) ? $params['role_type'] : '',
                                                                                                        'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                                                                                        'company_id' => $company->id,
                                                                                                        'department_id' =>$grandson->id,
                                                                                                    ])?>" onclick="javascript:window.location.href='<?= Url::to([$searchUrl,
                                                                                                        'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                                                                                        'role_type'=>isset($params['role_type']) ? $params['role_type'] : '',
                                                                                                        'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                                                                                        'company_id' => $company->id,
                                                                                                        'department_id' =>$grandson->id,
                                                                                                    ])?>';"><?= $grandson->name;?></a></span>
                                                                                                    </li>
                                                                                                <?php endif;?>
                                                                                            <?php endforeach;?>
                                                                                        <?php endif;?>
                                                                                    </ul>
                                                                                </li>
                                                                            <?php endif;?>
                                                                            <?php endforeach;?>
                                                                        <?php endif;?>
                                                                    </ul>
                                                                </li>
                                                            <?php endif;?>
                                                            <?php endforeach;?>
                                                        <?php endif;?>
                                                    </ul>
                                                </li>
                                            <?php endforeach;?>
                                        <?php endif;?>
                                    <?php endif;?>
                                    <?php if (!$administrator->isBelongCompany()):?>
                                        <li class="<?php if (isset($params['company_id']) && $params['company_id'] == '99999'):?>jstree-open<?php endif;?>">
                                    <span><a class="<?php if (isset($params['company_id']) && $params['company_id'] == '99999'):?>btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                            'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                            'role_type'=>isset($params['role_type']) ? $params['role_type'] : '',
                                            'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                            'company_id' =>'99999',
                                            'department_id' =>'',
                                        ])?>" onclick="javascript:window.location.href='<?= Url::to([$searchUrl,
                                            'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                            'role_type'=>isset($params['role_type']) ? $params['role_type'] : '',
                                            'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                            'company_id' =>'99999',
                                            'department_id' =>'',
                                        ])?>';">总公司</a></span></li>
                                    <?php endif;?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12" style="background:#fff;">
        		<?= LinkPager::widget(['pagination' => $pagination]);?>
        	</div>
        </div>
    </div>
</div>

<div class="modal fade" id="recruitment-modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">人员返聘确认</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">确定要返聘“<span id="hire-name"></span>”吗？请选择返聘后人员所属公司与部门。</div>
                <?php
                $companyOptions = ['class' => 'form-control', 'prompt'=>'请选择公司'];
                $departmentOptions = ['class' => 'form-control', 'prompt'=>'请选择部门'];
                $hireForm = new \backend\models\HireForm();
                $form = \yii\bootstrap\ActiveForm::begin([
                    'id' => 'hire-form',
//                    'action' => ['administrator/take-over'],
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-2',
                            'offset' => 'col-sm-offset-2',
                            'wrapper' => 'col-sm-8',
                        ],
                    ],
                ]); ?>
                <?= $form->field($hireForm, 'company_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                    'serverUrl' => \yii\helpers\Url::to(['company/ajax-list']),
                    'itemsName' => 'company',
                    'placeholderId' => '0',
                    'width' => '390px',
                    'placeholder' => '请选择公司',
                    'searchKeywordName' => 'keyword',
                    'eventSelect' => new JsExpression("
                               $('#department_id').val('0').trigger('change');
                                ")
                ])->label('所属公司');
                $companyUrl = \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']);
                echo $form->field($hireForm, 'department_id')->widget(Select2Widget::className(), [
                    'selectedItem' => [],
                    'options' => $departmentOptions,
                    'placeholderId' => '0',
                    'width' => '390px',
                    'placeholder' => '请选择部门',
                    'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']),
                    'itemsName' => 'department',
                    'eventOpening' => new JsExpression("
                        var id = $('#company_id').val();
                                serverUrl = '{$companyUrl}'.replace('__company_id__', id ? id : '-1');
                    ")
                ])->label('所属部门');?>
                <?= Html::activeHiddenInput($hireForm,'administrator_id') ?>
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary sure-btn">确定</button>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$statusUrl = \yii\helpers\Url::to(['administrator/status-manager']);
$hireUrl = \yii\helpers\Url::to(['administrator/ajax-hire']);
$this->registerJs(<<<JS
$(document).ready(function()
{
    $('#jstree1').jstree({
        'core' : {
            'check_callback' : true
        },
        'plugins' : [ 'types', 'dnd' ],
        'types' : {
            'default' : {
                'icon' : 'fa  fa-folder'
            },
        }
    });
});

$('.hire-btn').on('click',function()
{
	var id = $(this).attr('data-id');
	var name = $(this).attr('data-name');
	$('#hire-name').text(name);
	$('#administrator_id').val(id);
	$('.sure-btn').on('click',function(){
	$.post('{$hireUrl}',$('#hire-form').serialize(),function(rs)
	{
       if(rs.status != 200)
       {
	        $('.warning-active').html(rs.message);
	   }else{
	        window.location.reload();
	   }
	},'json')
	})
})

JS
);
?>