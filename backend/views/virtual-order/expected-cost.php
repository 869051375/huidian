<?php
/** @var $this \yii\web\View */
/* @var $provider \yii\data\ActiveDataProvider */
/** @var $model \common\models\VirtualOrder */

use common\utils\BC;
use common\utils\Decimal;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var \common\models\CostItem[] $cost */
$cost = $provider->query->all();
$costItemUrl = Url::to(['order-cost/create']);
?>
<?= $this->render('view',['model' => $model]) ?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="tabs-container">
                <?= $this->render('nav',['model' => $model]) ?>
                    <div class="tab-content">
                        <div class="panel-body" style="border-top: none">
                        <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>预计总成本管理</b>：</p>
                        <?php
                        $virtualOrderExpectedCost = new \backend\models\VirtualOrderExpectedCost();
                        $form = ActiveForm::begin([
                            'layout' => 'inline',
                            'action' => ['virtual-order-action/expected-cost'],
                            'validationUrl' => ['virtual-order-action/expected-cost', 'is_validate' => 1],
                            'enableAjaxValidation' => true,
                            'id' => 'expected-cost-form']); ?>
                        <b>成本名称*</b>
                        <div class="combobox form-control" style="border: none;">
                            <input type="text" id="cost-name">
                            <div>
                                <ul class="li-cost">
                                    <?php foreach ($cost as $item): ?>
                                        <li data-id="<?= $item->id ?>" data-price="<?= $item->price ?>"><?= $item->name ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if (Yii::$app->user->can('order-cost/*')): ?>
                                    <p>+新增其他成本</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <b>成本金额*</b>
                        <?= Html::activeHiddenInput($virtualOrderExpectedCost, 'virtual_order_id',['value' => $model->id]) ?>
                        <?= Html::activeHiddenInput($virtualOrderExpectedCost, 'cost_name',['class' => 'class-cost-name']) ?>
                        <?= $form->field($virtualOrderExpectedCost, 'cost_price')->textInput(['class' => 'form-control class-cost-price']) ?>
                        <b>备注</b>
                        <?= $form->field($virtualOrderExpectedCost, 'remark')->textInput() ?>

                        <?php if (Yii::$app->user->can('virtual-order-action/expected-cost')): ?>
                            <button type="submit" class="btn btn-w-m btn-primary cost-btn">增加</button>
                        <?php endif; ?>
                        <span class="text-danger warning-active"></span>
                        <?php
                        $this->registerJs(<<<JS
                        $(function() 
                        {
                            var form = $('#expected-cost-form');
                            form.on('beforeSubmit', function()
                            {
                                var form = $('#expected-cost-form');
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
                            });
                        });
JS
);
                        ?>
                        <?php ActiveForm::end(); ?>
                        <br>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 304px;">增加时间</th>
                                    <th class="text-center" style="width: 265px;">成本名称</th>
                                    <th class="text-center" style="width: 265px;">成本金额</th>
                                    <th class="text-center" style="width: 265px;">备注</th>
                                    <th class="text-center" style="width: 224px;">操作人</th>
                                </tr>
                                </thead>
                                <tbody id="cost-record-list">
                                <?php
                                $cost_price = 0;
                                /** @var \backend\models\VirtualOrderExpectedCost[] $virtualOrderExpectedCost */
                                $virtualOrderExpectedCost = $model->virtualOrderExpectedCost;
                                foreach ($virtualOrderExpectedCost as $cost):
                                $cost_price  += $cost->cost_price;
                                ?>
                                    <tr style="background: none;">
                                        <td class="text-center"><?= Yii::$app->formatter->asDatetime($cost->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                        <td class="text-center"><?= $cost->cost_name; ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($cost->cost_price); ?></td>
                                        <td class="text-center"><?= $cost->remark; ?></td>
                                        <td class="text-center"><?= $cost->creator_name; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background: none;">
                                    <td colspan="5" class="text-right">
                                        成本金额总计：<span class="text-danger"><?= Decimal::formatYenCurrentNoWrap($cost_price); ?></span>&nbsp;&nbsp;&nbsp;
                                        预计总利润：<span class="text-danger"><?= Decimal::formatYenCurrentNoWrap(BC::sub($model->total_amount,$cost_price)); ?></span>
                                        未计算分配预计成本金额：<span class="text-danger"><?= Decimal::formatYenCurrentNoWrap($model->getOrderTotalExpectedCost() == null ? $model->getTotalExpectedCost() : BC::sub( $model->getTotalExpectedCost(),$model->getOrderTotalExpectedCost())); ?></span>
                                        剩余可计算预计利润：<span class="text-danger"><?= Decimal::formatYenCurrentNoWrap(BC::sub(BC::sub($model->total_amount,$cost_price),$model->getTotalExpectedProfit())); ?></span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <p>说明：成本一旦增加后，不允许删除，若输入错误，可再增加一笔成本进行回冲，如多输入了一笔刻章费，300元，须再增加一笔刻章费，-300元，备注里务必说明情况</p>
                            <div class="hr-line-dashed"></div>
                            <span style="margin-right: 20px">已录入预计成本金额总计(不含已结转)：<?= Decimal::formatYenCurrentNoWrap($model->getOrderTotalExpectedCost() == null ? 0 : BC::sub($model->getOrderTotalExpectedCost(),$model->getKnotCostPrice())); ?></span>
                            <span style="margin-right: 20px">已结转过预计利润的成本金额总计：<?= Decimal::formatYenCurrentNoWrap($model->getKnotCostPrice()); ?></span>
                            <span style="margin-right: 20px">未计算分配预计成本金额总计：<?= Decimal::formatYenCurrentNoWrap($model->getOrderTotalExpectedCost() == null ? $model->getTotalExpectedCost() : BC::sub($model->getTotalExpectedCost(),$model->getOrderTotalExpectedCost())); ?></span>
                            <?php if (Yii::$app->user->can('virtual-order-action/calculate-expected-profit')): ?>
                            <button class="btn btn-warning calculate-expected-btn" <?php if(floatval($model->getTotalAmount()) || $model->payRate()): ?>disabled<?php endif; ?> data-target="#calculate-expected-cost-modal" data-toggle="modal">计算预计利润</button>
                            <?php endif; ?>
                            <div class="hr-line-dashed"></div>
                            <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>预计利润金额更正历史记录</b>：</p>
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 304px;">更正时间</th>
                                    <th class="text-center" style="width: 265px;">子订单号</th>
                                    <th class="text-center" style="width: 265px;">金额名称</th>
                                    <th class="text-center" style="width: 265px;">更正金额</th>
                                    <th class="text-center" style="width: 265px;">更正备注</th>
                                    <th class="text-center" style="width: 224px;">操作人</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($model->correctRecord as $correctRecord): ?>
                                    <tr style="background: none;">
                                        <td class="text-center"><?= Yii::$app->formatter->asDatetime($correctRecord->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                        <td class="text-center"><?= $correctRecord->sn; ?></td>
                                        <td class="text-center"><?= $correctRecord->title; ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($correctRecord->expected_profit); ?></td>
                                        <td class="text-center"><?= $correctRecord->remark; ?></td>
                                        <td class="text-center"><?= $correctRecord->creator_name; ?></td>
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
</div>
<!--成本类型库弹框开始-->
<div class="modal fade cost-modal" id="edit_cost" tabindex="-1" role="dialog" aria-labelledby="modal-title">
    <?php Pjax::begin(); ?>
    <?= $this->render('/order/cost',['provider' => $provider]) ?>
    <?php Pjax::end(); ?>
</div>
<!--成本类型库弹框结束-->
<!--计算预计利润start-->
<?php if (Yii::$app->user->can('virtual-order-action/calculate-expected-profit')): ?>
<div class="modal fade" id="calculate-expected-cost-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $calculateExpectedCost = new \backend\models\CalculateExpectedProfitForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['virtual-order-action/calculate-expected-profit'],
        'id' => 'calculate-expected-form',
        'validationUrl' => ['virtual-order-action/calculate-expected-profit', 'is_validate' => 1],
        'enableAjaxValidation' => true,
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-3',
                'offset' => 'col-sm-offset-3',
                'wrapper' => 'col-sm-8',
            ],
        ],
    ]);
    ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">计算预计利润</h4>
            </div>

            <div class="modal-body input_box clerk-div">
                <p class="calculate-info"></p>
                <p>确定要计算预计利润吗？</p>
                <p>预计利润计算成功后，计算金额将记录在相应的账簿中。</p>
                <p class="text-danger warning-active"><?= Html::error($calculateExpectedCost, 'virtual_order_id'); ?></p>
            </div>
            <div class="modal-footer">
                <?= Html::activeHiddenInput($calculateExpectedCost, 'virtual_order_id',['value' => $model->id]); ?>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary calculate-sure-btn">立即计算</button>
            </div>
        </div>
    </div>
    <?php
    $calculateInfoUrl = Url::to(['virtual-order-action/calculate-info']);
    $this->registerJs(<<<JS
    $(function() 
    {
        $('#calculateexpectedprofitform-month').change(function() 
        {
            calculateInfo();
        });
        
        function calculateInfo() 
        {
            var form = $('#calculate-expected-form');
            var month = $('#calculateexpectedprofitform-month').val();
            month = month ? month : 0; 
            $.get('{$calculateInfoUrl}',{month:month,virtual_order_id:'{$model->id}'},function(rs) 
            {
                if(rs['status'] === 200)
                {
                    form.find('.calculate-info').empty().html('你当前正在计算：'+rs['data']['num']+'个子订单，确认要计算吗？');
                }
                else
                {
                    form.find('.calculate-info').empty();
                }
            });
        }
        
        $('.calculate-expected-btn').click(function()
        {
            calculateInfo();
            var form = $('#calculate-expected-form');
            form.trigger('reset.yiiActiveForm');
            form.find('.warning-active').text('');
        });
        
        $('#calculate-expected-form').on('beforeSubmit', function()
        {
            var form = $(this);
            form.find('.calculate-sure-btn').text('计算中...').attr('disabled','disabled');
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
                    form.find('.calculate-sure-btn').empty().text('确定').removeAttr('disabled');
                }
            }, 'json');
            return false;
        });
    })
JS
    ) ?>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<?php endif; ?>
<!--计算预计利润end-->
<?php
$this->registerJs(<<<JS
//下拉框js
$(function()
{
    $('#cost-name').change(function() 
    {
        var cost_name = $('#virtualorderexpectedcost-cost_name');
        var order_cost_name = $(this).val();
        var record_cost_name = cost_name.val();
        if(order_cost_name && record_cost_name == '')
        {
           cost_name.val(order_cost_name);
        }
    });
    
	combobox('.combobox');
	function combobox(name)
	{
	    var  timer = null;
		$(name).find('input').focus(function()
		{
		    timeOut();
			$(this).siblings().show();
		});
		
		$(name).find('input').blur(function()
		{
		    clearInterval(timer);
			$(this).siblings().hide();
		});
		
		selValue();
		function timeOut()
		{
		    timer = setInterval(selValue(),20);
		}
		
        function selValue()
        {
           var li = $(name).children('div').find('li');
           li.mousedown(function()
           {
               var val = $(this).text();
               var price = $(this).attr('data-price');
               $(this).parents(name).find('input').val(val);
               $(this).parents(name).find('div').eq(1).hide();
               $('#virtualorderexpectedcost-cost_name').val(val);
               $('#virtualorderexpectedcost-cost_price').val(price);
           })
        }
     
		$(name).children('div').find('p').mousedown(function()
		{
            $(this).parents(name).find('div').hide();
            $('.cost-modal').modal('show');
            $('#edit_cost form').attr('action', '{$costItemUrl}');
		})
	}
});
JS
)?>