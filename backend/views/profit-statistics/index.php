<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/11/6
 * Time: 上午10:24
 */

use backend\models\UpdateRateForm;
use common\models\MonthProfitRecord;
use common\models\PersonMonthProfit;
use yii\bootstrap\Html;

/** @var PersonMonthProfit[] $models */
/** @var \common\models\MonthProfitRecord $record */
$this->title = '预计利润表';
$this->params['breadcrumbs'] = [$this->title];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5><?= $this->title ?></h5>
                <div class="ibox-tools">
                    <?php if (Yii::$app->user->can('expected-profit-settlement/*')): ?>
                        <a href="<?= \yii\helpers\Url::to(['expected-profit-settlement/index']) ?>" class="btn btn-primary btn-sm"><span
                                    class="fa fa-calendar"></span> 预计利润总结算</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ibox-content page-select2-area">
                <ul class="nav nav-tabs">
                    <li<?php if (Yii::$app->controller->action->id == 'index'): ?> class="active"<?php endif; ?>>
                        <a href="<?= \yii\helpers\Url::to(['index']) ?>">个人利润表</a>
                    </li>

                    <li<?php if (Yii::$app->controller->action->id == 'team'): ?> class="active"<?php endif; ?>>
                        <a href="<?= \yii\helpers\Url::to(['team']) ?>">团队利润表</a>
                    </li>

                    <li<?php if (Yii::$app->controller->action->id == 'department'): ?> class="active"<?php endif; ?>>
                        <a href="<?= \yii\helpers\Url::to(['department']) ?>">部门利润表</a>
                    </li>
                </ul>

                <?= Html::beginForm(['index'], 'get', ['role' => 'form', 'class' => 'form-inline']) ?>
                <div class="m-t-md">
                    <div class="form-group">
                        <b>选择时间</b>
                        <?= Html::dropDownList('id', $record ? $record->id : 0, MonthProfitRecord::getAllFinishMonth(), ['class' => 'form-control']) ?>
                    </div>
                    <button type="submit" class="btn btn-default">搜索</button>
                </div>
                <?= Html::endForm(); ?>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>排名</th>
                            <th>姓名</th>
                            <th>部门</th>
                            <th>职位</th>
                            <th>订单总额（元）</th>
                            <th>订单数量</th>
                            <th>总客户数</th>
                            <th>新客户数</th>
                            <th>更正前预计总利润（元）</th>
                            <th>预计总利润（元）</th>
                            <th>个人提成比例</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $s = 1;?>
                        <?php foreach($models as $i => $model):
                            if(empty($model->administrator_id)) continue; ?>
                            <tr>
                                <td><?= $s; ?></td>
                                <td><?= $model->administrator_name; ?></td>
                                <td><?= $model->department_name; ?></td>
                                <td><?= $model->title; ?></td>
                                <td><?= $model->order_amount; ?></td>
                                <td><?= $model->order_count; ?></td>
                                <td><?= $model->customer_count; ?></td>
                                <td><?= $model->new_customer_count; ?></td>
                                <td><?= \common\utils\BC::div($model->correct_front_expected_amount,100,4); ?></td><!--此处数据除以100只做页面展示使用-->
                                <td><?= \common\utils\BC::div($model->expected_profit,100,4); ?></td><!--此处数据除以100只做页面展示使用-->
                                <td>
                                    <?= $model->reward_proportion; ?>%
                                    <?php if (Yii::$app->user->can('profit-statistics/update-rate')): ?>
                                    <a class="update-rate-btn" data-target="#update-rate" data-toggle="modal" data-id="<?= $model->id ?>">修改</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php $s++;?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!--新增、编辑的弹框开始-->
<div class="modal fade" id="update-rate" tabindex="-1" role="dialog" aria-labelledby="modal-title">
    <?php
    $updateRateForm = new UpdateRateForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['profit-statistics/update-rate','is_validate' => 0],
        'validationUrl' => ['profit-statistics/update-rate','is_validate' => 1],
        'enableAjaxValidation' => true,
        'id' => 'update-rate-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-4',
                'wrapper' => 'col-sm-6',
                'hint' => 'col-sm-2'
            ],
        ],
    ]); ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">个人提成比例修改</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($updateRateForm, 'rate')->textInput()->hint('%')?>
                <?= \yii\helpers\Html::activeHiddenInput($updateRateForm,'person_month_profit_id') ?>
                <div class="form-group">
                    <label class="control-label col-sm-4">注意：</label>
                    <div class="col-sm-6">
                        个人提成比例修改之后，当前业务员的提成金额按照新的提成比例计算。
                    </div>
                </div>
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
$this->registerJs(<<<JS
$(function() 
{
    var form = $('#update-rate-form');
    $('.update-rate-btn').click(function()
    {
        var id = $(this).attr('data-id');
        $('#updaterateform-person_month_profit_id').val(id);
        form.trigger('reset.yiiActiveForm');
        form.find('.warning-active').empty();
    });

    form.on('beforeSubmit', function()
    {
        $.post(form.attr('action'), form.serialize(), function(rs)
        {
            if(rs.status !== 200)
            {
                form.find('.warning-active').text(rs.message);
            }
            else
            {
                window.location.reload();
            }
        },'json');
        return false;
    });
})
JS
);
?>
