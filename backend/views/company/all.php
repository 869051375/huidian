<?php

use backend\assets\CompanyAsset;
use backend\widgets\LinkPager;
use common\models\Administrator;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $provider yii\data\ActiveDataProvider */
/** @var \backend\models\CompanySearch $searchModel */
/** @var Administrator $administrator */
$administrator = Yii::$app->user->identity;
$this->title = '组织机构视图';
$this->params['breadcrumbs'][] = $this->title;
/** @var Administrator $models */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$searchUrl = 'company/all';
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
            <div class="ibox-title row">
                <h5>全部人员列表</h5>
                <div class="ibox-tools row">
                    <div class="col-sm-12 text-right">
                        <span class=" new-crate dropdown-toggle count-info btn-primary btn m-l-sm ">
                             添加人员 <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                    <ul class="dropdown-menu model-block" style="left:auto;right: 15px;margin-top:0;">
                        <li ></li>
                        <?php if (Yii::$app->user->can('administrator/add-manager')):?>
                            <li><a href="<?= Url::to(['administrator/add-manager', 'type' => Administrator::TYPE_ADMIN]);?>"><i class="fa fa-male"></i> 管理员</a></li>
                        <?php endif;?>
                        <?php if (Yii::$app->user->can('administrator/add-customer-service')):?>
                            <li><a href="<?= Url::to(['administrator/add-customer-service', 'type' => Administrator::TYPE_CUSTOMER_SERVICE]);?>"><i class="fa fa-male"></i> 客服</a></li>
                        <?php endif;?>
                        <?php if (Yii::$app->user->can('administrator/add-supervisor')):?>
                            <li><a href="<?= Url::to(['administrator/add-supervisor', 'type' => Administrator::TYPE_SUPERVISOR]);?>"><i class="fa fa-male"></i> 嘟嘟妹</a></li>
                        <?php endif;?>
                        <?php if (Yii::$app->user->can('administrator/add-clerk')):?>
                            <li><a href="<?= Url::to(['administrator/add-clerk', 'type' => Administrator::TYPE_CLERK]);?>"><i class="fa fa-male"></i> 服务人员</a></li>
                        <?php endif;?>
                        <?php if (Yii::$app->user->can('administrator/add-salesman') ):?>
                            <li><a href="<?= Url::to(['administrator/add-salesman', 'type' => Administrator::TYPE_SALESMAN]);?>"><i class="fa fa-male"></i> 业务员</a></li>
                        <?php endif;?>
                    </ul>
                </div>
            </div>
            <div class="ibox-content row">
                <div class=" personnel-list row">
                    <div>快捷筛选项：</div>
                    <div>
                        <ul>
                            <li><a class="<?php if (isset($params['department_type']) && $params['department_type'] == $searchModel::DEPARTMENT_TYPE_LEADER):?>btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                    'role_type'=>isset($params['role_type']) ? $params['role_type'] :'',
                                    'department_type'=>$searchModel::DEPARTMENT_TYPE_LEADER,
                                    'company_id' =>isset($params['company_id']) ? $params['company_id'] :'',
                                    'department_id' =>isset($params['department_id']) ? $params['department_id'] :'',
                                ])?>">部门负责人</a></li>
                            <li><a class="<?php if (isset($params['department_type']) && $params['department_type'] == $searchModel::DEPARTMENT_TYPE_MANAGER):?>btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                    'role_type'=>isset($params['role_type']) ? $params['role_type'] :'',
                                    'department_type'=>$searchModel::DEPARTMENT_TYPE_MANAGER,
                                    'company_id' =>isset($params['company_id']) ? $params['company_id'] :'',
                                    'department_id' =>isset($params['department_id']) ? $params['department_id'] :'',
                                ])?>">部门领导/助理</a></li>
                            <li><a class="<?php if (isset($params['department_type']) && $params['department_type'] == $searchModel::DEPARTMENT_TYPE_ASSIGN):?>btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                    'role_type'=>isset($params['role_type']) ? $params['role_type'] :'',
                                    'department_type'=>$searchModel::DEPARTMENT_TYPE_ASSIGN,
                                    'company_id' =>isset($params['company_id']) ? $params['company_id'] :'',
                                    'department_id' =>isset($params['department_id']) ? $params['department_id'] :'',
                                ])?>">商机指定分配人</a></li>
                            <li><a class="<?php if ((isset($params['department_type']) && $params['department_type'] == '')||!isset($params['department_type'])):?> btn btn-xs btn-success <?php else:?> text-danger <?php endif;?>" href="<?= Url::to([$searchUrl,
                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                    'role_type'=>isset($params['role_type']) ? $params['role_type'] :'',
                                    'department_type'=>'',
                                    'company_id' =>isset($params['company_id']) ? $params['company_id'] :'',
                                    'department_id' =>isset($params['department_id']) ? $params['department_id'] :'',
                                ])?>" >全部</a></li>
                        </ul>
                    </div>
                </div>
                <div class="personnel-list row">
                    <div>快捷筛选角色：</div>
                    <div>
                        <ul>
                            <li>
                                <a class="<?php if (isset($params['role_type']) && $params['role_type'] == $searchModel::ROLE_TYPE_CUSTOMER_SERVICE):?> btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                    'role_type'=>$searchModel::ROLE_TYPE_CUSTOMER_SERVICE,
                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                    'company_id' =>isset($params['company_id']) ? $params['company_id'] :'',
                                    'department_id' =>isset($params['department_id']) ? $params['department_id'] :'',
                                ])?>">客服</a>
                            </li>
                            <li>
                                <a class="<?php if (isset($params['role_type']) && $params['role_type'] == $searchModel::ROLE_TYPE_SUPERVISOR):?> btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                    'role_type'=>$searchModel::ROLE_TYPE_SUPERVISOR,
                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                    'company_id' =>isset($params['company_id']) ? $params['company_id'] :'',
                                    'department_id' =>isset($params['department_id']) ? $params['department_id'] :'',
                                ])?>">嘟嘟妹</a>
                            </li>
                            <li>
                                <a class="<?php if (isset($params['role_type']) && $params['role_type'] == $searchModel::ROLE_TYPE_CLERK):?> btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                    'role_type'=>$searchModel::ROLE_TYPE_CLERK,
                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                    'company_id' =>isset($params['company_id']) ? $params['company_id'] :'',
                                    'department_id' =>isset($params['department_id']) ? $params['department_id'] :'',
                                ])?>">服务人员</a>
                            </li>
                            <li>
                                <a class="<?php if (isset($params['role_type']) && $params['role_type'] == $searchModel::ROLE_TYPE_SALESMAN):?> btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                    'role_type'=>$searchModel::ROLE_TYPE_SALESMAN,
                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                    'company_id' =>isset($params['company_id']) ? $params['company_id'] :'',
                                    'department_id' =>isset($params['department_id']) ? $params['department_id'] :'',
                                ])?>">业务人员</a>
                            </li>
                            <li><a class="<?php if (isset($params['role_type']) && $params['role_type'] == $searchModel::ROLE_TYPE_ADMIN):?> btn btn-xs btn-success<?php endif;?>" href="<?= Url::to([$searchUrl,
                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                    'role_type'=>$searchModel::ROLE_TYPE_ADMIN,
                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                    'company_id' =>isset($params['company_id']) ? $params['company_id'] :'',
                                    'department_id' =>isset($params['department_id']) ? $params['department_id'] :'',
                                ])?>">管理员</a></li>
                            <li><a class="<?php if ((isset($params['role_type']) && $params['role_type'] == '') || !isset($params['role_type'])):?> btn btn-xs btn-success <?php else:?> text-danger <?php endif;?>" href="<?= Url::to([$searchUrl,
                                    'keyword'=> isset($params['keyword']) ? $params['keyword'] : '',
                                    'role_type'=>'',
                                    'department_type'=>isset($params['department_type']) ? $params['department_type'] :'',
                                    'company_id' =>isset($params['company_id']) ? $params['company_id'] :'',
                                    'department_id' =>isset($params['department_id']) ? $params['department_id'] :'',
                                ])?>">全部</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="ibox-content row">
                <?php
                $labelOptions = ['labelOptions' => ['class' => false]];
                $form = ActiveForm::begin([
                    'action' => ['company/all'],
                    'layout' => 'inline',
                    'method' => 'get',
                ]); ?>
                <?= $form->field($searchModel, 'keyword', $labelOptions)->textInput(['placeholder'=>'请输入姓名/手机号']) ?>
                <?= \yii\helpers\Html::activeHiddenInput($searchModel, 'role_type', ['value' => isset($params['role_type']) ? $params['role_type'] : ''])?>
                <?= \yii\helpers\Html::activeHiddenInput($searchModel, 'department_type', ['value' => isset($params['department_type']) ? $params['department_type'] : ''])?>
                <?= \yii\helpers\Html::activeHiddenInput($searchModel, 'company_id', ['value' => isset($params['company_id']) ? $params['company_id'] : ''])?>
                <?= \yii\helpers\Html::activeHiddenInput($searchModel, 'department_id', ['value' => isset($params['department_id']) ? $params['department_id'] : ''])?>
                <button type="submit" class="btn btn-primary">搜索</button>
                <a class="btn btn-danger" href="<?= Url::to(['company/all'])?>">重置</a>
                <?php ActiveForm::end(); ?>

            </div>
            <div class="ibox-content row">
                <div class="col-xs-9 row">
                    <table class="table table-bordered row">
                        <thead>
                        <tr>
                            <th class="text-center">编号ID</th>
                            <th class="text-center">账号类型</th>
                            <th class="text-center">真实姓名/手机号</th>
                            <th class="text-center">邮箱</th>
                            <th class="text-center">创建日期</th>
                            <th class="text-center">所属公司/部门</th>
                            <th class="text-center">职位</th>
                            <th class="text-center">最后登陆时间</th>
                            <th class="text-center">状态</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($models)):?>
                            <?php /** @var Administrator $model */
                            foreach ($models as $model):
                                if($model->is_root) continue;
                                $managerOptions = [
                                    'id' => false,
                                    'class' => 'change-status-checkbox',
                                    'label' => false,
                                    'data-id' => $model->id,
                                    'data-type' => $model->type,
                                    'data-url' => '/administrator/status-manager',
                                ];

                                $customerServiceOptions = [
                                    'id' => false,
                                    'class' => 'change-status-checkbox',
                                    'label' => false,
                                    'data-id' => $model->id,
                                    'data-type' => $model->type,
                                    'data-url' => '/administrator/status-customer-service',
                                ];

                                $supervisorOptions = [
                                    'id' => false,
                                    'class' => 'change-status-checkbox',
                                    'label' => false,
                                    'data-id' => $model->id,
                                    'data-type' => $model->type,
                                    'data-url' => '/administrator/status-supervisor',
                                ];

                                $clerkOptions = [
                                    'id' => false,
                                    'class' => 'change-status-checkbox',
                                    'label' => false,
                                    'data-id' => $model->id,
                                    'data-type' => $model->type,
                                    'data-url' => '/administrator/status-clerk',
                                ];

                                $salesmanOptions = [
                                    'id' => false,
                                    'class' => 'change-status-checkbox',
                                    'label' => false,
                                    'data-id' => $model->id,
                                    'data-type' => $model->type,
                                    'data-url' => '/administrator/status-salesman',
                                ];
                                if($model->is_root == 1)
                                {
                                    $managerOptions['readonly'] = 'readonly';
                                }
                                if(!Yii::$app->user->can('administrator/status-manager'))
                                {
                                    $managerOptions['readonly'] = 'readonly';
                                }
                                if(!Yii::$app->user->can('administrator/status-customer-service'))
                                {
                                    $customerServiceOptions['readonly'] = 'readonly';
                                }
                                if(!Yii::$app->user->can('administrator/status-supervisor'))
                                {
                                    $supervisorOptions['readonly'] = 'readonly';
                                }
                                if(!Yii::$app->user->can('administrator/status-clerk'))
                                {
                                    $clerkOptions['readonly'] = 'readonly';
                                }
                                if(!Yii::$app->user->can('administrator/status-salesman'))
                                {
                                    $salesmanOptions['readonly'] = 'readonly';
                                }
                                ?>
                                <tr>
                                    <td><?= $model->id;?></td>
                                    <td><?= $model->getTypeName();?></td>
                                    <td><?= $model->name;?>/<?= $model->phone;?></td>
                                    <td><?= $model->email;?></td>
                                    <td><?= Yii::$app->formatter->asDatetime($model->created_at);?></td>
                                    <td>
                                        <?php if ($model->company && $model->department):?>
                                            <?= $model->company ? $model->company->name :'';?>/<?= $model->department ? $model->department->name : '';?>
                                        <?php endif;?>
                                    </td>
                                    <td><?= $model->title;?></td>
                                    <td>
                                        <?= $model->administratorLog ? Yii::$app->formatter->asDatetime($model->administratorLog->created_at) :''?>
                                    </td>
                                    <td>
                                        <label>
                                            <?php if ($model->type == Administrator::TYPE_ADMIN):?>
                                                <?= Html::activeCheckbox($model, 'status', $managerOptions); ?>
                                            <?php endif;?>
                                            <?php if ($model->type == Administrator::TYPE_CUSTOMER_SERVICE):?>
                                                <?= Html::activeCheckbox($model, 'status', $customerServiceOptions); ?>
                                            <?php endif;?>
                                            <?php if ($model->type == Administrator::TYPE_SUPERVISOR):?>
                                                <?= Html::activeCheckbox($model, 'status', $supervisorOptions); ?>
                                            <?php endif;?>
                                            <?php if ($model->type == Administrator::TYPE_CLERK):?>
                                                <?= Html::activeCheckbox($model, 'status', $clerkOptions); ?>
                                            <?php endif;?>
                                            <?php if ($model->type == Administrator::TYPE_SALESMAN):?>
                                                <?= Html::activeCheckbox($model, 'status', $salesmanOptions); ?>
                                            <?php endif;?>
                                        </label>
                                    </td>
                                    <td class="text-center">
                                        <?php if(Yii::$app->user->can('administrator/force-login')):?>
                                            <a class="btn btn-xs btn-link"
                                               href="<?= Yii::$app->urlManager->createUrl(['/administrator/force-login', 'id' => $model->id]) ?>">Force Login</a>
                                        <?php endif; ?>
                                        <?php if (Yii::$app->user->can('administrator/update-salesman') && $model->type == Administrator::TYPE_SALESMAN): ?>
                                            <a class="btn btn-xs btn-white" href="<?= Yii::$app->urlManager->createUrl(['/administrator/salesman-update', 'id' => isset($model->salesman->id) ? $model->salesman->id : '']) ?>">编辑业务员</a>
                                            <a class="btn btn-xs btn-white"
                                               href="<?= Yii::$app->urlManager->createUrl(['/administrator/update-salesman', 'id' => $model->id, 'type' => $model->type]) ?>">编辑</a>
                                        <?php elseif (Yii::$app->user->can('administrator/update-clerk') && $model->type == Administrator::TYPE_CLERK): ?>
                                            <a class="btn btn-xs btn-white" href="<?= Yii::$app->urlManager->createUrl(['/administrator/clerk-update', 'id' => isset($model->clerk->id) ? $model->clerk->id : '']) ?>">编辑服务人员</a>
                                            <a class="btn btn-xs btn-white"
                                               href="<?= Yii::$app->urlManager->createUrl(['/administrator/update-clerk', 'id' => $model->id, 'type' => $model->type])?>">编辑</a>
                                        <?php elseif (Yii::$app->user->can('administrator/update-supervisor') && $model->type == Administrator::TYPE_SUPERVISOR): ?>
                                            <a class="btn btn-xs btn-white" href="<?= Yii::$app->urlManager->createUrl(['/administrator/supervisor-update', 'id' => $model->supervisor->id]) ?>">编辑嘟嘟妹</a>
                                            <a class="btn btn-xs btn-white"
                                               href="<?= Yii::$app->urlManager->createUrl(['/administrator/update-supervisor', 'id' => $model->id, 'type' => $model->type]) ?>">编辑</a>
                                        <?php elseif (Yii::$app->user->can('administrator/update-customer-service') && $model->type == Administrator::TYPE_CUSTOMER_SERVICE): ?>
                                            <a class="btn btn-xs btn-white" href="<?= Yii::$app->urlManager->createUrl(['/administrator/customer-service-update', 'id' => isset($model->customerService->id) ? $model->customerService->id : '']) ?>">编辑客服</a>
                                            <a class="btn btn-xs btn-white"
                                               href="<?= Yii::$app->urlManager->createUrl(['/administrator/update-customer-service', 'id' => $model->id, 'type' => $model->type]) ?>">编辑</a>
                                        <?php elseif (Yii::$app->user->can('administrator/update-manager')):?>
                                            <a class="btn btn-xs btn-white"
                                               href="<?= Yii::$app->urlManager->createUrl(['/administrator/update-manager', 'id' => $model->id, 'type' => $model->type]) ?>">编辑</a>
                                        <?php endif; ?>
                                        <?php if($model->is_root == 0): ?>
                                            <?php if(Yii::$app->user->can('administrator/change-jobs')): ?>
                                                <a class="btn btn-xs btn-white"
                                                   href="<?= Url::to(['administrator/change-jobs', 'id' => $model->id]) ?>">调岗</a>
                                            <?php endif; ?>
                                            <?php if(Yii::$app->user->can('administrator/leave')
                                                && ($model->type == Administrator::TYPE_ADMIN
                                                    || $model->type == Administrator::TYPE_SALESMAN)): ?>
                                                <a class="btn btn-xs btn-white"
                                                   href="<?= Url::to(['administrator/leave', 'id' => $model->id]) ?>">离职</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                        <?php endif;?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="12">
                                <?= LinkPager::widget(['pagination' => $pagination]);?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="col-xs-3 row">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content">
                            <h3>组织机构</h3>
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
                        <div class="ibox-content">
                            <?php if (Yii::$app->user->can('department/list')):?>
                                <a href="<?= Url::to(['company/list'])?>"><span class="btn btn-primary btn-sm">维护公司与部门</span></a>
                            <?php endif;?>
                            <?php if (Yii::$app->user->can('role/list')):?>
                                <a href="<?= Url::to(['role/list'])?>"><span class="btn btn-primary btn-sm">角色</span></a>
                            <?php endif;?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="up-down-modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">修改账号状态</h4>
            </div>
            <div class="modal-body">
                确定禁用吗?
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary sure-btn">确定</button>
            </div>
        </div>
    </div>
</div>
<?php
$statusUrl = \yii\helpers\Url::to(['administrator/status-manager']);
$this->registerJs(<<<JS

    $(".new-crate").mouseenter(function()
    {
        $(".model-block").show();
        chevronDown();
    }).mouseleave(function(){
        $(".model-block").hide();
        chevronUp();
    });
    $(".model-block").mouseenter(function(){
        $(this).show();
        chevronDown();
    }).mouseleave(function(){
        $(".model-block").hide();
        chevronUp();
    });
    
    function chevronUp() {
        $('.new-crate').find('i').removeClass('fa-chevron-up');
        $('.new-crate').find('i').addClass('fa-chevron-down');
    }
    
    function chevronDown() {
        $('.new-crate').find('i').removeClass('fa-chevron-down');
        $('.new-crate').find('i').addClass('fa-chevron-up');
    }
    //状态修改
    var currentCheckbox = null;
    var statusList = document.querySelectorAll('.change-status-checkbox');
    var statusSwitchery = null;
    var modal = $('#up-down-modal');
    for(var i = 0; i < statusList.length; i++)
    {
        statusSwitchery = new Switchery(statusList[i], {"size":"small","className":"switchery"});
        (function (checkbox){
            $(checkbox.element).click(function(){
                modal.find('.warning-active').empty().text('');
                var status = checkbox.isChecked() ? 0 : 1;
                if(status === 0)
                {
                    modal.find('.modal-body').text('确定启用吗？');
                }
                else
                {
                    modal.find('.modal-body').text('确定禁用吗？');
                }
                modal.modal('show');
                currentCheckbox = checkbox;
                return false;
            });
        })(statusSwitchery);
    }
    
    modal.find('.sure-btn').click(function(){
        changeStatus(currentCheckbox);
    });
    
    function changeStatus(checkbox)
    {
        var status = checkbox.isChecked() ? 0 : 1;
        $.post($(checkbox.element).attr('data-url'), {status: status, id: $(checkbox.element).attr('data-id'), type: $(checkbox.element).attr('data-type')}, function(rs){
            if(rs.status === 200)
            {
                checkbox.setPosition(true);
                checkbox.handleChange();
                modal.modal('hide');
            }
            else 
            {
                modal.find('.warning-active').empty().text(rs.message);
            }
        }, 'json');
    }
    
$(document).ready(function(){
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
JS
);
?>