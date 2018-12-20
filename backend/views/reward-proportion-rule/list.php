<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use common\models\CrmDepartment;
use common\models\RewardProportion;
use common\models\RewardProportionRule;
use common\models\RewardProportionVersion;
use zhuravljov\yii\widgets\DateTimePicker;

/** @var RewardProportionRule[] $models */
/** @var RewardProportionVersion $version */

$this->title = date('Y-m-d H:i:s',$version->created_at).'版本维护';
$this->params['breadcrumbs'] = [['label' => '提成比例方案维护', 'url' => ['/reward-proportion/list']],
    ['label' => date('Y-m-d H:i:s',$version->created_at), 'url' => ['/reward-proportion-version/list','id' => $version->reward_proportion_id]],
    $this->title];
?>
<!--方案规则列表开始-->
<div class="row">
    <div class="col-xs-12">
        <div class="tabs-container">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel-body">
                        <div class="ibox-tools">
                            <?php if(empty($version->effective_month) && Yii::$app->user->can('reward-proportion/update')): ?>
                            <a href="#" class="btn btn-primary btn-sm add-rule" data-target="#edit_rule"
                               data-toggle="modal">新增提成比例范围</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>预计总利润金额范围（元）</th>
                                <th>提成比例</th>
                                <?php if(empty($version->effective_month)): ?>
                                <th class="text-right">操作</th>
                                <?php endif; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($models as $key => $model):?>
                                <tr class="list-item" data-id="<?= $model->id; ?>">
                                    <td><?= '≥'.$model->expected_total_profit; ?></td>
                                    <td><?= $model->reward_proportion.'%' ?></td>
                                <?php if(isset($version->effective_month)): ?>
                                    <td class="text-right">
                                    <?php if (Yii::$app->user->can('reward-proportion/delete') && empty($version->effective_month)): ?>
                                    <span class="btn btn-xs btn-white del-btn" data-target="#del_rule"
                                          data-toggle="modal">删除</span>
                                    <?php endif; ?>
                                    <?php if (Yii::$app->user->can('reward-proportion/update') && empty($version->effective_month)): ?>
                                    <span class="btn btn-xs btn-white edit-btn" data-target="#edit_rule"
                                          data-toggle="modal">编辑</span>
                                    <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--方案规则列表结束-->

<!--删除方案规则弹窗开始-->
<div class="modal fade" id="del_rule" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">提成比例删除</h4>
                </div>
                <div class="modal-body">
                    确定删除当前提成比例吗?
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary sure-btn">确定</button>
                </div>
            </div>
        </div>
</div>
<!--删除方案规则弹窗结束-->

<!--新增、编辑的弹框开始-->
<div class="modal fade" id="edit_rule" tabindex="-1" role="dialog" aria-labelledby="modal-title">
        <?php
        $versionForm = new RewardProportionRule();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['reward-proportion-rule/create'],
            'validationUrl' => ['reward-proportion-rule/validation'],
            'enableAjaxValidation' => true,
            'id' => 'rule-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-5',
                    'offset' => 'col-sm-offset-2',
                    'wrapper' => 'col-sm-3',
                    'hint' => 'col-sm-2'
                ],
            ],
        ]); ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">提成比例维护</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($versionForm, 'expected_total_profit')->textInput()?>
                    <?= $form->field($versionForm, 'reward_proportion')->textInput()->hint('%')?>
                    <?= \yii\helpers\Html::activeHiddenInput($versionForm,'reward_proportion_version_id',['value' => $version->id]) ?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary effective-sure-btn">保存</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<!--新增、编辑的弹框结束-->

<?php
$deleteUrl = \yii\helpers\Url::to(['delete']);
$createUrl = \yii\helpers\Url::to(['create']);
$updateUrl = \yii\helpers\Url::to(['update','id' => '__id__']);
$detailUrl = \yii\helpers\Url::to(['detail','id' => '__id__']);
$this->registerJs(<<<js
//删除
$('.del-btn').on('click',function()
{
	var id = $(this).parents('.list-item').attr('data-id');
	$('.sure-btn').on('click',function(){
	$.post('{$deleteUrl}',{id:id},function(rs)
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

//添加
$('.add-rule').on('click',function(){
        $('.modal form').attr('action', '{$createUrl}');
        $('#rule-form').trigger('reset.yiiActiveForm');
});

//修改
$('.edit-btn').click(function() 
{
  $('#rule-form').trigger('reset.yiiActiveForm');
        var id = $(this).parents('.list-item').attr('data-id');
        $('.modal form').attr('action', '{$updateUrl}'.replace('__id__', id));
        $.get('{$detailUrl}'.replace('__id__', id),function(rs){
            if(rs.status!=200)
            {
              
            }else{
                $('#rewardproportionrule-expected_total_profit').val(rs.model.expected_total_profit);
                $('#rewardproportionrule-reward_proportion').val(rs.model.reward_proportion);
            }
        },'json')   
})

js
);
?>