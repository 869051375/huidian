<?php

/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use common\models\FixedPoint;
use yii\helpers\Url;

/* @var $provider yii\data\ActiveDataProvider */
$this->title = '提成方案管理';
$this->params['breadcrumbs'][] = $this->title;
/** @var \common\models\FixedPoint[] $models */
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
                                    <a href="#" class="btn btn-primary btn-sm add-rule" data-target="#add-rule"
                                       data-toggle="modal"><span class="fa fa-plus"></span> 新增方案</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="panel-body">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>方案ID</th>
                                    <th>创建时间</th>
                                    <th>固定点位名称</th>
                                    <th>固定点位</th>
                                    <th>生效状态</th>
                                    <th class="text-right">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($models as $model): ?>
                                    <tr class="list-item">
                                        <td><?= $model->id; ?></td>
                                        <td><?= $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '--'; ?></td>
                                        <td><?= $model->name ?></td>
                                        <td><?= $model->rate.'%'; ?></td>
                                        <td><?= $model->status ? '已生效' : '未生效'; ?></td>
                                        <td class="text-right">
                                            <?php if (Yii::$app->user->can('reward-proportion/delete')): ?>
                                                <span class="btn btn-xs btn-white delete-btn" data-target="#del-rule" data-id="<?= $model->id; ?>" data-text="确定删除当前提成方案“<?= $model->name ?>”吗？"
                                                      data-toggle="modal">删除</span>
                                            <?php endif; ?>
                                            <?php if ($model->status == FixedPoint::STATUS_DISABLED && Yii::$app->user->can('reward-proportion/update')): ?>
                                                <span class="btn btn-xs btn-white update-btn" data-target="#add-rule" data-id="<?= $model->id; ?>"
                                                      data-toggle="modal">编辑</span>
                                                <span class="btn btn-xs btn-white effective-btn" data-target="#effective-rule-model" data-id="<?= $model->id; ?>" data-text="确定生效当前提成方案“<?= $model->name ?>”吗？"
                                                      data-toggle="modal">生效</span>
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
<div class="modal fade" id="add-rule" role="dialog" aria-labelledby="modal-title">
    <?php
    $fixedPoint = new \common\models\FixedPoint();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['reward-proportion/create-point'],
        'validationUrl' => ['reward-proportion/create-point','is_validate' => 1],
        'enableAjaxValidation' => true,
        'id' => 'fixed-point-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-3',
                'offset' => 'col-sm-offset-2',
                'wrapper' => 'col-sm-6',
                'hint' => 'col-sm-offset-3'
            ],
        ],
    ]); ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">固定点位算法方案新增</h4>
            </div>
            <div class="modal-body input_box">
            <?= \yii\helpers\Html::activeHiddenInput($fixedPoint,'id'); ?>
            <?= $form->field($fixedPoint,'name')->textInput(); ?>
            <?= $form->field($fixedPoint,'rate')->textInput()->hint('%'); ?>
            <div class="form-group">
                <label class="control-label col-sm-3"></label>
                <div class="col-sm-6">
                    固定点位输入为大于0的整数，最大不超过100，如输入10，代表当前的值为10%。
                </div>
            </div>
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
<div class="modal fade" id="del-rule" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">固定点位算法方案删除</h4>
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

<!--生效弹窗开始-->
<div class="modal fade" id="effective-rule-model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">固定点位算法方案生效</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary effective-sure-btn">确定</button>
            </div>
        </div>
    </div>
</div>
<!--生效弹窗结束-->

<?php
$createUrl = \yii\helpers\Url::to(['create-point']);
$effectiveUrl = \yii\helpers\Url::to(['effective']);
$updateUrl = \yii\helpers\Url::to(['create-point','id' => '__id__']);
$detailUrl = \yii\helpers\Url::to(['point-detail','id' => '__id__']);
$deleteUrl = \yii\helpers\Url::to(['point-delete','id' => '__id__']);
$this->registerJs(<<<js


$(function() 
{
    var form = $('#fixed-point-form');
    
    //添加
    $('.add-rule').on('click',function()
    {
        $('#fixedpoint-id').val(0);
        form.attr('action', '{$createUrl}');
        form.trigger('reset.yiiActiveForm');
        form.find('.modal-title').empty().text('固定点位算法方案新增');
    });
    
    form.on('beforeSubmit',function() 
    {
        $.post(form.attr('action'), form.serialize(), function(rs)
        {
            if(rs.status === 200)
            {
                form.trigger('reset.yiiActiveForm');
                window.location.reload();
            }
            else
            {
                form.find('.warning-active').text(rs.message);
            }
        }, 'json');
        return false;
    })
    
    //修改
    $('.update-btn').click(function() 
    {
        form.find('.modal-title').empty().text('固定点位算法方案编辑');
        form.trigger('reset.yiiActiveForm');
        var id = $(this).attr('data-id');
        form.attr('action', '{$updateUrl}'.replace('__id__', id));
        $.get('{$detailUrl}'.replace('__id__', id),function(rs)
        {
            if(rs.status === 200)
            {
               $('#fixedpoint-id').val(rs.model.id);
               $('#fixedpoint-name').val(rs.model.name);
               $('#fixedpoint-rate').val(rs.model.rate);
            }
        },'json')   
    });
    
    //删除
    $('.delete-btn').on('click',function()
    {
        var id = $(this).attr('data-id');
        var text_name = $(this).attr('data-text');
        $('#del-rule').find('.modal-body').text(text_name);
        $('.warning-active').html('');
        $('.del-sure-btn').on('click',function(){
        $.post('{$deleteUrl}',{id:id},function(rs)
        {
            if(rs.status !== 200)
            {
                $('.warning-active').html(rs.message);
            }else{
                window.location.reload();
            }
         },'json')
        });
    });
    
    //生效
    $('.effective-btn').on('click',function()
    {
        var model = $('#effective-rule-model');
        var id = $(this).attr('data-id');
        var text_name = $(this).attr('data-text');
        model.find('.modal-body').text(text_name);
        model.find('.warning-active').html('');
        $('.effective-sure-btn').on('click',function(){
        $.post('{$effectiveUrl}',{id:id},function(rs)
        {
            if(rs.status !== 200)
            {
                model.find('.warning-active').html(rs.message);
            }else{
                window.location.reload();
            }
         },'json')
        });
    });
  
})


js
);
?>