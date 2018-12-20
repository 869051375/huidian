<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use common\models\CrmDepartment;
use common\models\RewardProportion;
use common\models\RewardProportionVersion;
use zhuravljov\yii\widgets\DateTimePicker;

/** @var RewardProportionVersion[] $models */
/** @var RewardProportion $proportion */

$this->title = $proportion->name.'版本维护';
$this->params['breadcrumbs'] = [['label' => '提成比例方案维护', 'url' => ['/reward-proportion/list']], $this->title];
?>
<!--方案列表开始-->
<div class="row">
    <div class="col-xs-12">
        <div class="tabs-container">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel-body">
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>版本ID</th>
                                <th>创建时间</th>
                                <th>生效时间</th>
                                <th>最后操作人/时间</th>
                                <th class="text-right">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($models as $key => $model):?>
                                <tr class="list-item" data-id="<?= $model->id; ?>">
                                    <td><?= $key+1; ?></td>
                                    <td><?= date('Y-m-d H:i:s',$model->created_at); ?></td>
                                    <td><?= $model->effective_month; ?></td>
                                    <td>
                                        <?= $model->updater_name; ?><br>
                                        <?= Yii::$app->formatter->asDatetime($model->updated_at); ?>
                                    </td>
                                    <td class="text-right">
                                    <?php if (Yii::$app->user->can('reward-proportion/list')): ?>
                                    <a class="btn btn-xs btn-white copy-btn"  href="<?= \yii\helpers\Url::to(['reward-proportion-rule/list','id' => $model->id]) ?>">
                                        <?= (empty($model->effective_month) && Yii::$app->user->can('reward-proportion/update'))?'编辑':'详情'; ?>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (Yii::$app->user->can('reward-proportion/update')): ?>
                                        <?php if(count($models) == $key+1 && isset($model->effective_month)): ?>
                                        <span class="btn btn-xs btn-white copy-btn" data-target="#copy_version"
                                              data-toggle="modal">复制</span>
                                        <?php endif; ?>
                                        <?php if(empty($model->effective_month)): ?>
                                        <span class="btn btn-xs btn-white effective-btn" data-target="#effective_version"
                                              data-toggle="modal">生效</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--方案列表结束-->

<!--删除方案弹窗开始-->
<div class="modal fade" id="copy_version" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">复制版本</h4>
                </div>
                <div class="modal-body">
                    确定要复制吗?
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary sure-btn">确定</button>
                </div>
            </div>
        </div>
</div>
<!--删除方案弹窗结束-->

<!--生效的弹框开始-->
<div class="modal fade" id="effective_version" tabindex="-1" role="dialog" aria-labelledby="modal-title">
        <?php
        $versionForm = new \backend\models\RewardProportionVersionForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['reward-proportion-version/effective'],
            'validationUrl' => ['reward-proportion-version/validation'],
            'enableAjaxValidation' => true,
            'id' => 'effective-form',
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
                    <h4 class="modal-title">提成比例方案维护</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($versionForm, 'effective_month')->widget(DateTimePicker::className(), [
                        'clientOptions' => [
                            'format' => 'yyyy-mm',
                            'language' => 'zh-CN',
                            'autoclose' => true,
                            'minView' => 'year',
                            'startView' => 'year',
                        ],
                        'clientEvents' => []]) ?>
                    <?= \yii\helpers\Html::activeHiddenInput($versionForm,'version_id'); ?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary effective-sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<!--生效的弹框结束-->

<?php
$copyUrl = \yii\helpers\Url::to(['copy']);
$effectiveUrl = \yii\helpers\Url::to(['effective','id' => '__id__']);
$this->registerJs(<<<js
//复制
$('.copy-btn').on('click',function()
{
    var version_id = $(this).parents('.list-item').attr('data-id');
	$('.sure-btn').on('click',function(){
	$.post('{$copyUrl}',{version_id:version_id},function(rs)
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

$('.effective-btn').click(function() 
{
   var id = $(this).parents('.list-item').attr('data-id');
   $('#rewardproportionversionform-version_id').val(id);
})


//生效
$('#effective-form').on('beforeSubmit', function()
{
    $.post($(this).attr('action'), $('#effective-form').serialize(), function(rs)
    {
         if(rs['status'] === 200)
         {
             window.location.reload();
         }
         else
         {
             $('.warning-active').text(rs.message);
         }
    }, 'json');
        return false;
});

js
);
?>