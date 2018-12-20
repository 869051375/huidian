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
$uniqueId = Yii::$app->controller->action->uniqueId;
?>
<?= $this->render('view',['model' => $model]) ?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="tabs-container">
            <?= $this->render('nav',['model' => $model]) ?>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <div class="payment-button">
                            <div class="clearfloat">
                                <a href="<?= Url::to(['virtual-order/cost','vid' => $model->id]) ?>" class="<?php if($uniqueId == 'virtual-order/cost'): ?>payment-button-active<?php endif; ?>">实际成本</a>
                                <a href="<?= Url::to(['virtual-order/score','vid' => $model->id]) ?>" class="<?php if($uniqueId == 'virtual-order/score'): ?>payment-button-active<?php endif; ?>">实际利润</a>
                            </div>
                        </div>
                        <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>实际总成本管理</b>：</p>
                        <?php
                        $virtualOrderCost = new \backend\models\VirtualOrderCost();
                        $form = ActiveForm::begin([
                            'layout' => 'inline',
                            'action' => ['virtual-order-action/cost'],
                            'validationUrl' => ['virtual-order-action/cost', 'is_validate' => 1],
                            'enableAjaxValidation' => true,
                            'id' => 'cost-form']); ?>
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
                        <?= Html::activeHiddenInput($virtualOrderCost, 'virtual_order_id',['value' => $model->id]) ?>
                        <?= Html::activeHiddenInput($virtualOrderCost, 'cost_name',['class' => 'class-cost-name']) ?>
                        <?= $form->field($virtualOrderCost, 'cost_price')->textInput(['class' => 'form-control class-cost-price']) ?>
                        <b>备注</b>
                        <?= $form->field($virtualOrderCost, 'remark')->textInput() ?>

                        <?php if (Yii::$app->user->can('order-cost-record/*')): ?>
                            <button type="submit" class="btn btn-primary cost-btn" style="width: 94px;height: 34px;">增加</button>
                        <?php endif; ?>
                        <span class="text-danger warning-active"></span>
                        <?php
                        $this->registerJs(<<<JS
                        $(function() 
                        {
                            var form = $('#cost-form');
                            form.on('beforeSubmit', function()
                            {
                                var form = $('#cost-form');
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
                                /** @var \backend\models\VirtualOrderCost[] $virtualOrderCost */
                                $virtualOrderCost = $model->virtualOrderCost;
                                foreach ($virtualOrderCost as $cost):
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
                                        <span class="text-danger" style="margin-right: 10px;">成本金额总计：<?= Decimal::formatCurrentYuan($cost_price); ?></span>
                                        <span class="text-danger">未分配实际成本金额：<?= Decimal::formatCurrentYuan(BC::sub($model->getTotalCost(),$model->getOrderCost())); ?></span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <p>说明：成本一旦增加后，不允许删除，若输入错误，可再增加一笔成本进行回冲，如多输入了一笔刻章费，300元，须再增加一笔刻章费，-300元，备注里务必说明情况！</p>
                        <div class="hr-line-dashed"></div>
                        <span style="margin-right: 20px">已分配成本金额：<?= Decimal::formatCurrentYuan($model->getOrderCost()); ?></span>
                        <span style="margin-right: 20px">未分配成本金额：<?= Decimal::formatCurrentYuan(BC::sub($model->getTotalCost(),$model->getOrderCost())); ?></span>
                        <?php if(floatval(BC::sub($model->getTotalCost(),$model->getOrderCost())) && Yii::$app->user->can('virtual-order-action/calculate-profit')): ?>
                        <button class="btn btn-primary btn-sm calculate-expected-btn" data-target="#calculate-cost-modal" data-toggle="modal">立即分配</button>
                        <?php endif; ?>
                        <p style="margin-top: 15px;">
                            <span style="margin-right: 10px;">子订单可计算实际利润总金额：<?= Decimal::formatCurrentYuan($model->getPerformance()); ?></span>
                            <?php if(floatval($model->getPerformance()) && Yii::$app->user->can('virtual-order-action/calculate-profit')): ?>
                            <button class="btn btn-primary btn-sm calculate-profit-btn" data-target="#calculate-profit-modal" data-toggle="modal">计算提成</button>
                            <?php endif; ?>
                        </p>
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

<!--下放成本开始-->
<div class="modal fade" id="calculate-cost-modal" role="dialog" aria-labelledby="myModalLabel">
    <?php
    $calculateCostForm = new \backend\models\CalculateCostForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['virtual-order-action/drop-cost'],
        'id' => 'calculate-cost-form',
        'validationUrl' => ['virtual-order-action/drop-cost', 'is_validate' => 1],
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
                <h4 class="modal-title" id="myModalLabel">计算分配成本</h4>
            </div>
            <div class="modal-body">
                <p>确定要立即计算分配实际成本吗？</p>
                <p class="text-danger warning-active"></p>
            </div>
            <div class="modal-footer">
                <?= Html::activeHiddenInput($calculateCostForm, 'virtual_order_id',['value' => $model->id]); ?>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary calculate-cost-btn">计算分配</button>
            </div>
        </div>
    </div>
    <?php
    $this->registerJs(<<<JS
    $(function() 
    {
        var form = $('#calculate-cost-form');
        $('.calculate-expected-btn').click(function() 
        {
            form.find('.warning-active').empty();
        });
        
        form.on('beforeSubmit', function()
        {
            form.find('.calculate-cost-btn').text('分配中...').attr('disabled','disabled');
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
                    form.find('.calculate-cost-btn').empty().text('确定').removeAttr('disabled');
                }
            }, 'json');
            return false;
        });
    });
JS
);
    ?>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<!--下放成本结束-->

<!--计算业绩开始-->
<div class="modal fade" id="calculate-profit-modal" role="dialog" aria-labelledby="myModalLabel">
    <?php
    $calculateProfitForm = new \backend\models\CalculateProfitForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['virtual-order-action/calculate-profit'],
        'id' => 'calculate-profit-form',
        'validationUrl' => ['virtual-order-action/calculate-profit', 'is_validate' => 1],
        'enableAjaxValidation' => true,
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-3',
                'wrapper' => 'col-sm-7',
                'hint' => 'col-sm-2',
            ],
        ],
    ]);
    ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">计算提成</h4>
            </div>
            <div class="modal-body">
                <?= $form->field($calculateProfitForm, 'rate')->textInput()->hint('%'); ?>
                <div class="form-group">
                    <label class="control-label col-sm-3"></label>
                    <div class="col-sm-7 text-danger">计算当前月的实际利润金额的百分之多少。</div>
                </div>
                <?= $form->field($calculateProfitForm, 'point')->checkbox(); ?>
                <?= $form->field($calculateProfitForm, 'fix_point_id')->dropDownList(\common\models\FixedPoint::getFixPoint()); ?>
                <?= Html::activeHiddenInput($calculateProfitForm, 'virtual_order_id',['value' => $model->id]); ?>
            </div>
            <div class="modal-footer">
                <span class="warning-active text-danger"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary calculate-cost-btn">立即计算</button>
            </div>
        </div>
    </div>
    <?php
    $this->registerJs(<<<JS
$(function() 
{
    $('.field-calculateprofitform-fix_point_id').hide();
    var form = $('#calculate-profit-form');
    $('.calculate-profit-btn').click(function() 
    {
        form.trigger('reset.yiiActiveForm');
        form.find('.warning-active').empty();
    });
    $('#calculateprofitform-point').click(function() 
    {
        if($(this).is(':checked'))
        {
            $('.field-calculateprofitform-fix_point_id').show();
        }
        else
        {
            $('.field-calculateprofitform-fix_point_id').hide();
        }
    });
    
    form.on('beforeSubmit', function()
    {
        form.find('.calculate-cost-btn').text('计算中...').attr('disabled','disabled');
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
                form.find('.calculate-cost-btn').empty().text('确定').removeAttr('disabled');
            }
        }, 'json');
        return false;
    });
});
JS
    );
    ?>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<!--计算业绩结束-->

<?php
$this->registerJs(<<<JS
//下拉框js
$(function()
{
    $('#cost-name').change(function() 
    {
        $('#virtualordercost-cost_name').val($(this).val());
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
		$(name).find('input').blur(function(){
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
          li.mousedown(function(){
           var val = $(this).text();
           var price = $(this).attr('data-price');
           $(this).parents(name).find('input').val(val);
           $(this).parents(name).find('div').eq(1).hide();
           $('#virtualordercost-cost_name').val(val);
		   $('#virtualordercost-cost_price').val(price);
        })
        }
     
		$(name).children('div').find('p').mousedown(function(){
		$(this).parents(name).find('div').hide();
		$('.cost-modal').modal('show');
		$('#edit_cost form').attr('action', '{$costItemUrl}');
		})
	}
});
JS
)?>