<?php

/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use common\models\CrmDepartment;
use common\models\RewardProportion;
use yii\helpers\Url;

/* @var $provider yii\data\ActiveDataProvider */
$this->title = '提成方案管理';
$this->params['breadcrumbs'][] = $this->title;
/** @var RewardProportion[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
$uniqueId = Yii::$app->controller->action->uniqueId;

?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="tabs-container">
                <div class="row">
                    <div class="col-lg-12">
                        <ul class="nav nav-tabs">
                            <li <?php if($uniqueId == 'reward-proportion/list'): ?>class="active"<?php endif;?>>
                                <a href="<?= Url::to(['reward-proportion/list']) ?>">阶梯算法方案管理</a>
                            </li>
                            <li <?php if($uniqueId == 'reward-proportion/fixed-point'): ?>class="active"<?php endif;?>>
                                <a href="<?= Url::to(['reward-proportion/fixed-point']) ?>">固定点位算法方案管理</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!--方案列表-->
                <div class="tab-content">
                    <div class="tab-pane active">
                        <div class="panel-body">
                            <div class="ibox-tools">
                                <?php if (Yii::$app->user->can('reward-proportion/update')): ?>
                                    <a href="#" class="btn btn-primary btn-sm add-proportion" data-target="#add_proportion"
                                       data-toggle="modal" data-whatever="新增方案"><span class="fa fa-plus"></span> 新增方案</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="panel-body">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>方案ID</th>
                                    <th>方案名称</th>
                                    <th>关联部门</th>
                                    <th>创建时间</th>
                                    <th class="text-right">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($models as $model): ?>
                                    <tr class="list-item" data-id="<?= $model->id ?>">
                                        <td><?= $model->id; ?></td>
                                        <td>
                                            <a <?php if (Yii::$app->user->can('reward-proportion/list')): ?>href="<?= \yii\helpers\Url::to(['reward-proportion-version/list','id' => $model->id]) ?>"<?php endif; ?>>
                                                <?= $model->name ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if(isset($model->department)): ?>
                                                <?php
                                                /** @var  CrmDepartment[] $departments */
                                                $departments = $model->department;
                                                foreach($departments as $department): ?>
                                                    <?= $department->name; ?><br>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= Yii::$app->formatter->asDatetime($model->created_at); ?></td>
                                        <td class="text-right">
                                            <?php if (Yii::$app->user->can('reward-proportion/update')): ?>
                                                <span class="btn btn-xs btn-white update-btn" data-target="#add_proportion"
                                                      data-toggle="modal" data-whatever="编辑方案">修改</span>
                                            <?php endif; ?>
                                            <?php if (Yii::$app->user->can('reward-proportion/delete')): ?>
                                                <span class="btn btn-xs btn-white delete-btn" data-target="#del_proportion"
                                                      data-toggle="modal">删除</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
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
                <!--方案列表-->
            </div>
        </div>
    </div>
</div>

<!--新增方案开始-->
<div class="modal fade" id="add_proportion" tabindex="-1" role="dialog" aria-labelledby="modal-title">
    <?php
    $rewardProportion = new \common\models\RewardProportion();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['reward-proportion/create'],
        'validationUrl' => ['reward-proportion/validation'],
        'enableAjaxValidation' => true,
        'id' => 'proportion-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-2',
                'offset' => 'col-sm-offset-2',
                'wrapper' => 'col-sm-8',
                'hint' => 'col-sm-offset-2 col-sm-8'
            ],
        ],
    ]); ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">新增方案</h4>
            </div>
            <div class="modal-body input_box">
            <?= $form->field($rewardProportion,'name')->textInput(); ?>
            <input type="hidden" id="reward-proportion-rule" name="rule">
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
            </div>
        </div>
    </div>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<!--新增方案结束-->

<!--删除方案弹窗开始-->
<div class="modal fade" id="del_proportion" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">删除方案</h4>
            </div>
            <div class="modal-body">
                确定删除吗?
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary del-sure-btn">确定</button>
            </div>
        </div>
    </div>
</div>
<!--删除方案弹窗结束-->

<?php
$createUrl = \yii\helpers\Url::to(['create']);
$updateUrl = \yii\helpers\Url::to(['update','id' => '__id__']);
$detailUrl = \yii\helpers\Url::to(['detail','id' => '__id__']);
$deleteUrl = \yii\helpers\Url::to(['delete','id' => '__id__']);
$this->registerJs(<<<js
//添加
$('.add-proportion').on('click',function(){
        $('.modal form').attr('action', '{$createUrl}');
        $('#proportion-form').trigger('reset.yiiActiveForm');
});

//修改
$('.update-btn').click(function() 
{
    $('#proportion-form').trigger('reset.yiiActiveForm');
    var id = $(this).parents('.list-item').attr('data-id');
    $('.modal form').attr('action', '{$updateUrl}'.replace('__id__', id));
    $.get('{$detailUrl}'.replace('__id__', id),function(rs)
    {
        if(rs.status!=200)
        {
                  
        }else{
            $('#rewardproportion-name').val(rs.model.name);
        }
    },'json')   
});
//更改模态框标题
$('#add_proportion').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var recipient = button.data('whatever');
    var modal = $(this);
    modal.find('.modal-title').text(recipient);
});
//删除
$('.delete-btn').on('click',function()
{
    $('.warning-active').html('');
	var id = $(this).parents('.list-item').attr('data-id');
	$('.del-sure-btn').on('click',function(){
	$.post('{$deleteUrl}',{id:id},function(rs)
	{
	    if(rs.status != 200)
	    {
	        $('.warning-active').html(rs.message);
	    }else{
	        window.location.reload();
	    }
	    },'json')
	});
});

js
);
?>