<?php

/* @var $this yii\web\View */
/* @var $provider \yii\data\ActiveDataProvider */

use backend\widgets\LinkPager;
use common\models\Administrator;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var \backend\models\AdministratorRole $role  */

/** @var \common\models\Administrator[] $models  */
$models = $provider->getModels();
$pagination = $provider->getPagination();

$this->title = '成员管理';
$this->params['breadcrumbs'] = [
    ['label' => '角色管理', 'url' => ['list']],
    $this->title
];
$actionName = Yii::$app->controller->action->uniqueId;
?>
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <ul class="nav nav-tabs">
                    <li <?php if($actionName == 'role/update'): ?>class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['update','id' => $role->id]) ?>">角色编辑</a>
                    </li>
                    <li <?php if($actionName == 'role/member'): ?>class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['member','id' => $role->id]) ?>">成员管理</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <table class="footable table table-striped">
                            <thead>
                            <tr style="border-top: 1px solid #e7eaec;">
                                <th>姓名</th>
                                <th>手机号</th>
                                <th>所属公司</th>
                                <th>所属部门</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if($models): ?>
                                <?php foreach ($models as $model): ?>
                                    <tr>
                                        <td><?= $model->name; ?></td>
                                        <td><?= $model->phone; ?></td>
                                        <td><?= $model->company ? $model->company->name : '--'; ?></td>
                                        <td><?= $model->department? $model->department->name : '--'; ?></td>
                                        <td>
                                            <?php if(Yii::$app->user->can('administrator/force-login')):?>
                                                <a class="btn btn-xs btn-link"
                                                   href="<?= Yii::$app->urlManager->createUrl(['/administrator/force-login', 'id' => $model->id]) ?>">Force Login</a>
                                            <?php endif; ?>
                                            <?php if($model->type == Administrator::TYPE_CUSTOMER_SERVICE): ?>
                                            <a class="btn-white btn btn-xs" target="_blank"
                                               href="<?= yii\helpers\Url::to(['administrator/update-customer-service', 'id' => $model->id,'type' => $model->type]) ?>">编辑</a>
                                            <?php elseif($model->type == Administrator::TYPE_SUPERVISOR): ?>
                                                <a class="btn-white btn btn-xs" target="_blank"
                                                   href="<?= yii\helpers\Url::to(['administrator/update-supervisor', 'id' => $model->id,'type' => $model->type]) ?>">编辑</a>
                                            <?php elseif($model->type == Administrator::TYPE_CLERK): ?>
                                                <a class="btn-white btn btn-xs" target="_blank"
                                                   href="<?= yii\helpers\Url::to(['administrator/update-clerk', 'id' => $model->id,'type' => $model->type]) ?>">编辑</a>
                                            <?php elseif($model->type == Administrator::TYPE_SALESMAN): ?>
                                                <a class="btn-white btn btn-xs" target="_blank"
                                                   href="<?= yii\helpers\Url::to(['administrator/update-salesman', 'id' => $model->id,'type' => $model->type]) ?>">编辑</a>
                                            <?php elseif($model->type == Administrator::TYPE_ADMIN): ?>
                                                <a class="btn-white btn btn-xs" target="_blank"
                                                   href="<?= yii\helpers\Url::to(['administrator/update-manager', 'id' => $model->id,'type' => $model->type]) ?>">编辑</a>
                                            <?php endif; ?>
                                        </td>
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
            </div>
        </div>
    </div>
</div>
