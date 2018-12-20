<?php
/** @var $this \yii\web\View */
/* @var $provider \yii\data\ActiveDataProvider */

use backend\widgets\LinkPager;
use common\models\BusinessSubject;
use common\utils\Decimal;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var $model \common\models\VirtualOrder */
/** @var $status int  */
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
$companyOptions = ['class' => 'form-control', 'prompt'=>'请选择公司'];
$departmentOptions = ['class' => 'form-control', 'prompt'=>'请选择业务员'];

/** @var \common\models\Order[] $models  */
$models = $provider->getModels();
$pagination = $provider->getPagination();
$uniqueId = Yii::$app->controller->action->uniqueId;
?>
<?= $this->render('view',['model' => $model]) ?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="tabs-container">
                <?= $this->render('nav',['model' => $model]) ?>
                <div class="tab-content">
                    <!--子订单列表-->
                    <div class="tab-pane active">
                        <div class="panel-body" style="padding: 0;">
                            <div class="no-borders no-paddings" style="padding:0;">
                                <div style="overflow: hidden;">
                                    <div class="payment-button">
                                        <div class="clearfloat">
                                            <a href="<?= Url::to(['virtual-order/cost','vid' => $model->id]) ?>" class="<?php if($uniqueId == 'virtual-order/cost'): ?>payment-button-active<?php endif; ?>">实际成本</a>
                                            <a href="<?= Url::to(['virtual-order/score','vid' => $model->id]) ?>" class="<?php if($uniqueId == 'virtual-order/score'): ?>payment-button-active<?php endif; ?>">实际利润</a>
                                        </div>
                                    </div>
                                    <div class="batch-modifying">
                                        <div class="clearfloat">
                                            <?php if (Yii::$app->user->can('virtual-order-action/batch-calculate-profit')): ?>
                                                <button class="btn btn-primary batch-calculate-btn"
                                                        data-target="#calculate-profit-modal"
                                                        data-toggle="modal">批量计算提成</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div style="padding: 20px;">
                                        <table class="table table-bordered" style="border: none;margin: 0;">
	                                        <thead>
	                                        <tr style="border-top: 1px solid #e7eaec;">
	                                            <th class="text-center" style="width:80px;padding:0;vertical-align: middle;"><?= Html::checkbox('check', false,['label' => '选择','id' => 'check-all']); ?></th>
	                                            <th style="width:158px;vertical-align: middle;">订单信息</th>
	                                            <th style="width:193px;vertical-align: middle;">商品信息</th>
	                                            <th style="width:171px;vertical-align: middle;">业务人员</th>
	                                            <th class="text-center" style="width:76px;vertical-align: middle;">客服人员</th>
	                                            <th class="text-center" style="width:76px;vertical-align: middle;">服务人员</th>
	                                            <th class="text-center" style="width:108px;vertical-align: middle;">付款方式</th>
	                                            <th style="width:171px;vertical-align: middle;">支付信息</th>
	                                            <th class="text-center" style="width:76px;vertical-align: middle;">订单状态</th>
	                                            <th class="text-center" style="width:76px;vertical-align: middle;">财务明细<br>编号</th>
	                                            <th class="text-center" style="width:76px;vertical-align: middle;">订单业绩<br>提点月</th>
	                                            <th class="text-center" style="width:76px;vertical-align: middle;">剩余可计算实际利润总金额</th>
	                                            <th class="text-center" style="width:126px;vertical-align: middle;">操作</th>
	                                        </tr>
	                                        </thead>
	                                        <tbody>
	                                        <?php foreach($models as $order): ?>
	                                            <tr class="cls-opt">
	                                            <!-- 选择 -->
	                                            <td class="text-center" style="vertical-align: middle;">
	                                                <?php if(!($order->is_installment == 0 && $order->isUnpaid())): ?>
	                                                <?= Html::checkbox('check', false, ['value' => $order->id]); ?>
                                                    <?php endif; ?>
	                                            </td>

	                                            <!-- 订单信息 -->
	                                            <td style="vertical-align: middle;">
	                                                <p class="text-muted"><?= Yii::$app->formatter->asDatetime($order->created_at) ?></p>
	                                                <p>订单号：
                                                        <?php if (Yii::$app->user->can('virtual-order-action/detail') || $order->hasDetail()): ?>
	                                                        <a href="<?= Url::to(['order/info', 'id' => $order->id]) ?>" target="_blank"><?= $order->sn; ?></a>
	                                                    <?php else: ?>
	                                                        <?= $order->sn; ?>
	                                                    <?php endif; ?>
	                                                </p>
	                                                <p class="text-muted"><?= $order->getSourceAppName();?></p>
	                                                <p class="text-muted"><?= $order->is_proxy ? $order->creator_name.'后台新增' : '客户自主下单'; ?></p>
	                                            </td>

	                                            <!-- 商品信息 -->
	                                            <td style="vertical-align: middle;">
	                                                <p><?= $order->product_name; ?></p>
	                                                <p><?= $order->getArea(); ?></p>
	                                                <?php if (!empty($order->businessSubject)):?>
	                                                    <?php if(Yii::$app->user->can('business-subject/detail')): ?>
	                                                        <a href="<?= Url::to(['business-subject/information','id'=>$order->businessSubject->id]) ?>" target="_blank">
	                                                            <?php if ($order->businessSubject->subject_type == BusinessSubject::SUBJECT_TYPE_DISABLED):?>
	                                                                <?= $order->businessSubject->company_name; ?>
	                                                            <?php else:?>
	                                                                <?= $order->businessSubject->region; ?>
	                                                            <?php endif;?>
	                                                        </a>
	                                                    <?php else: ?>
	                                                        <?php if ($order->businessSubject->subject_type == BusinessSubject::SUBJECT_TYPE_DISABLED):?>
	                                                            <?= $order->businessSubject->company_name; ?>
	                                                        <?php else:?>
	                                                            <?= $order->businessSubject->region; ?>
	                                                        <?php endif;?>
	                                                    <?php endif;?>
	                                                <?php endif;?>
	                                            </td>

	                                            <!--业务人员-->
	                                            <td style="vertical-align: middle;">
	                                            	<div class="clearfloat">
	                                            		<p>负责人：</p>
		                                                <p>
		                                                    <?= $order->salesman_name; ?>
		                                                    <?php if($order->salesman_name): ?>
		                                                        <span class="divide_rate<?= $order->id ?>"><?= '('.$order->getDivideRate().'%'.')'; ?></span>
		                                                    <?php endif; ?>
		                                                </p>
	                                            	</div>

	                                                <?php if($order->salesman_name && !$order->is_vest): ?>
	                                                    <p>共享人：</p>
	                                                    <span class="order-list-team<?= $order->id ?>">
	                                                    <?php foreach($order->orderTeams as $team):?>
	                                                        <span data-team-id="<?= $team->id ?>">
	                                                            <?= $team->administrator_name ?>
	                                                            <?php if($team->divide_rate): ?>
	                                                                <?= '('.$team->divide_rate.'%)' ?>
	                                                            <?php endif;?>
	                                                        </span>
	                                                        <br>
	                                                    <?php endforeach; ?>
	                                                    </span>
	                                                <?php endif; ?>
	                                            </td>

	                                            <!-- 客服人员 -->
	                                            <td style="vertical-align: middle;">
	                                                <?= $order->customer_service_name; ?>
	                                            </td>

	                                            <!-- 服务人员 -->
	                                            <td style="vertical-align: middle;">
	                                                <?= $order->clerk_name; ?>
	                                            </td>

	                                            <!-- 付款方式 -->
	                                            <td style="vertical-align: middle;">
	                                                <p><?= $order->is_installment ? '分期付款' : '一次付款'; ?></p>
	                                            </td>

	                                            <!-- 支付信息 -->
	                                            <td  style="vertical-align: middle;">
	                                                <p>商品金额：<?= Decimal::formatCurrentYuan($order->original_price, 2) ?></p>
	                                                <?php if($order->package_remit_amount > 0): ?>
	                                                    <p>套餐优惠：<?= Decimal::formatCurrentYuan(-$order->package_remit_amount, 2, [], [], true) ?></p>
	                                                <?php endif; ?>
	                                                <?php if($order->wx_remit_amount > 0): ?>
	                                                    <p>微信下单优惠：<?= Decimal::formatCurrentYuan(-$order->wx_remit_amount, 2, [], [], true) ?></p>
	                                                <?php endif; ?>
	                                                <?php if(abs($order->adjust_amount) > 0): ?>
	                                                    <p>变动金额：<?= Decimal::formatCurrentYuan($order->adjust_amount, 2, [], [], true) ?></p>
	                                                <?php endif; ?>
	                                                <?php if($order->coupon_remit_amount > 0): ?>
	                                                    <p>优惠券金额：<?= Decimal::formatCurrentYuan($order->coupon_remit_amount, 2) ?></p>
	                                                <?php endif; ?>
	                                                <?php if(abs($order->adjust_amount) <= 0 && $order->wx_remit_amount <= 0 && $order->package_remit_amount <= 0 && $order->coupon_remit_amount <= 0): ?>
	                                                    <p>优惠金额：<?= Decimal::formatCurrentYuan('0.00', 2) ?></p>
	                                                <?php endif; ?>
	                                                <p>应付金额：<?= Decimal::formatCurrentYuan($order->price, 2) ?></p>
	                                                <?php if ($order->tax > 0): ?>
	                                                    <p class="text-muted">
	                                                        <small>(含税<?= Decimal::formatCurrentYuan($order->tax, 2) ?>)</small>
	                                                    </p>
	                                                <?php endif; ?>
	                                                <p>已付金额：<?= $order->payment_amount; ?></p>
	                                                <p <?php if($order->getPendingPayAmount()): ?>class="text-danger"<?php else:?>class="text-primary"<?php endif; ?>>
	                                                    未付金额：<?= $order->getPendingPayAmount(); ?></p>
	                                                <?php if($model->isPendingPayment() && Yii::$app->user->can('order-action/adjust-price')): ?>
	                                                    <button<?php if(empty($order->salesman_aid)):?> disabled="disabled"<?php endif; ?> class="btn btn-xs btn-default adjust-price-btn" data-id="<?= $order->id; ?>" data-original-price="<?= $order->price; ?>"
	                                                                                                                                       data-target="#adjust-order-price-modal" data-toggle="modal">
	                                                        <?php if($order->isAdjustStatusNotAdjust()):?>
	                                                            <?php if(empty($order->salesman_aid)):?>
	                                                                无负责人不可修改价格
	                                                            <?php else:?>
	                                                                修改价格
	                                                            <?php endif; ?>
	                                                        <?php elseif($order->isAdjustStatusPending()):?>
	                                                            修改价格审核中
	                                                        <?php elseif($order->isAdjustStatusPass()):?>
	                                                            修改价格审核已通过
	                                                        <?php elseif($order->isAdjustStatusReject()):?>
	                                                            修改价格审核未通过
	                                                        <?php endif; ?>
	                                                    </button>
	                                                <?php endif; ?>
	                                            </td>

	                                            <!--以下是订单状态-->
	                                            <td class="status" style="vertical-align: middle; text-align: center;">
                                                    <?php if ($order->isRefundApply() || $order->isRefundAudit()): ?>
                                                        <!--退款中的状态-->
                                                        <p><strong><?= $order->getRefundStatusName() ?></strong></p>
                                                        <p class="text-muted text-left">
                                                            <small>退款原因：<?= $order->getRefundReasonText() ?></small>
                                                        </p>
                                                        <?php if ($order->isRefundAudit()): ?>
                                                            <p class="text-muted text-left">
                                                            <small>
                                                                退款金额：<?= Yii::$app->formatter->asCurrency($order->refund_amount); ?></small>
                                                            </p><?php endif; ?>
                                                        <?php if ($order->isRefundApply()): ?>
                                                            <p class="text-muted text-left">
                                                            <small>
                                                                要求退款：<?= Yii::$app->formatter->asCurrency($order->require_refund_amount); ?></small>
                                                            </p><?php endif; ?>
                                                        <?php if (!empty($order->refund_remark)): ?>
                                                            <p class="text-muted text-left">
                                                            <small>说明：<?= $order->refund_explain; ?></small>
                                                            </p><?php endif; ?>
                                                        <?php if ($order->isRefundAudit() && !empty($order->refund_remark)): ?>
                                                            <p class="text-muted text-left">
                                                            <small>备注：<?= $order->refund_remark; ?></small>
                                                            </p><?php endif; ?>

                                                    <?php elseif ($order->isInService()): ?>
                                                        <?php if ($order->flow): ?>
                                                            <?php $flowHint = $order->getHintOperator($order->getCurrentNode()); ?>
                                                            <p>
                                                                <span class="<?php if ($order->isWarning()): ?>text-danger<?php endif; ?>"><?= $flowHint['content'] ?></span>
                                                            </p>
                                                            <?php if ($order->isWarning()): ?>
                                                                <p>开始报警时间：<?= Yii::$app->formatter->asDatetime($order->next_node_warn_time); ?></p>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            服务中
                                                        <?php endif; ?>
                                                    <?php elseif ($model->isCanceled()): ?>
                                                        <?php if($order->isBreakService() && $order->break_reason > 0):?>
                                                            <?= $order->getBreakReason()?>
                                                        <?php else:?>
                                                            已取消
                                                        <?php endif;?>
                                                    <?php else: ?>
                                                        <p>
                                                            <strong>
                                                                <?php if($order->isBreakService() && $order->break_reason > 0):?>
                                                                    <?= $order->getBreakReason()?>
                                                                <?php elseif($model->isUnpaid()):?>
                                                                    <?= $model->getPayStatus() ?>
                                                                <?php else:?>
                                                                    <?= $order->getStatusName(); ?>
                                                                <?php endif;?>
                                                            </strong>
                                                        </p>

                                                        <?php if ($order->isRefund()): ?>
                                                            <p class="text-muted"><?= $order->getRefundStatusName() ?></p>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
	                                            </td>

	                                            <!--财务明细编号-->
	                                            <td style="vertical-align: middle; text-align: center;">
	                                                <?= $order->financial_code ? $order->financial_code : '--'; ?>
	                                            </td>

	                                            <!--订单业绩提点月-->
	                                            <td style="vertical-align: middle; text-align: center;">
	                                                <?= $order->settlement_month ? mb_substr($order->settlement_month,0,4).'.'.mb_substr($order->settlement_month,4,2) : '--'; ?>
	                                            </td>

                                                <!--剩余可计算业绩总金额-->
                                                <td style="vertical-align: middle; text-align: center;">
                                                    <?= Decimal::formatYenCurrentNoWrap($order->getSurplusPerformance()); ?>
                                                </td>

	                                            <!--操作-->
	                                            <td style="vertical-align: middle; text-align: center;">
                                                    <?php if (Yii::$app->user->can('virtual-order-action/batch-calculate-profit')): ?>
	                                                <span class="btn btn-xs btn-primary see-order-follow-record m-t-xs calculate-profit-btn"
	                                                      data-target="#calculate-profit-modal" data-toggle="modal" data-order-id="<?= $order->id; ?>"
	                                                      data-id="<?= $order->virtual_order_id ?>">计算提成</span>
                                                    <?php endif; ?>
	                                            </td>
	                                        </tr>
	                                        <?php endforeach; ?>
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
                    <!--子订单列表-->
                </div>
            </div>
        </div>
    </div>
</div>

<!--计算业绩开始-->
<div class="modal fade" id="calculate-profit-modal" role="dialog" aria-labelledby="myModalLabel">
    <?php
    $calculateProfitForm = new \backend\models\BatchCalculateProfitForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['virtual-order-action/batch-calculate-profit'],
        'id' => 'calculate-profit-form',
        'validationUrl' => ['virtual-order-action/batch-calculate-profit', 'is_validate' => 1],
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
                <?= Html::activeHiddenInput($calculateProfitForm, 'order_id'); ?>
                <?= Html::activeHiddenInput($calculateProfitForm, 'virtual_order_id',['value' => $model->id]); ?>
            </div>
            <div class="modal-footer">
                <span class="warning-active text-danger"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary calculate-btn">立即计算</button>
            </div>
        </div>
    </div>
    <?php
    $this->registerJs(<<<JS
$(function() 
{
    $('.field-batchcalculateprofitform-fix_point_id').hide();

    $('.calculate-profit-btn').click(function() 
    {
        $('#batchcalculateprofitform-order_id').val($(this).attr('data-order-id'));
        form.trigger('reset.yiiActiveForm');
        form.find('.warning-active').empty();
    });
    
    var form = $('#calculate-profit-form');
    $('.batch-calculate-btn').click(function() 
    {
        var order_ids = checkedValues();
        $('#batchcalculateprofitform-order_id').val(order_ids);
        form.trigger('reset.yiiActiveForm');
        form.find('.warning-active').empty();
    });
    
    $('#batchcalculateprofitform-point').click(function() 
    {
        if($(this).is(':checked'))
        {
            $('.field-batchcalculateprofitform-fix_point_id').show();
        }
        else
        {
            $('.field-batchcalculateprofitform-fix_point_id').hide();
        }
    });
    
    form.on('beforeSubmit', function()
    {
        form.find('.calculate-btn').text('计算中...').attr('disabled','disabled');
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
                form.find('.calculate-btn').empty().text('确定').removeAttr('disabled');
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
$('#check-all').click(function() 
{
    if($(this).is(':checked'))
    {
        $('.cls-opt').find('input').prop("checked",true);
    }
    else
    {
        $('.cls-opt').find('input').prop("checked",false);
    }
});
//获取列表复选框选中的值
function checkedValues()
{
    var _this = $('.cls-opt');
    var order_ids = '';
    for(var i = 0; i < _this.length; i++)
    {
        var checkBox = _this.eq(i).find('input');
        if(checkBox.is(':checked'))
        {
           order_ids += checkBox.val()+',';
        }
    }
    return order_ids;
};
JS
)
?>