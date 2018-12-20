<?php

use backend\models\OrderSearch;
use backend\widgets\LinkPager;
use common\models\AdjustOrderPrice;
use common\models\BusinessSubject;
use common\models\Order;
use common\utils\BC;
use common\utils\Decimal;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var \yii\web\View $this */
/** @var \yii\data\DataProviderInterface $dataProvider */
/** @var \common\models\Order[] $models */
/** @var \backend\models\OrderSearch $searchModel */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$actionUniqueId = Yii::$app->controller->action->uniqueId;
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
$this->title = '订单金额变动审核';
$this->params['breadcrumbs'] = [$this->title];
$orderAuditedCount = Order::getOrderAuditedCount($administrator);
$pendingPayCount = Order::getPendingPayCount($administrator);
$pendingAssignCount = Order::getPendingAssignCount($administrator);
$pendingServiceCount = Order::getPendingServiceCount($administrator);
$timeoutCount = Order::getTimeoutCount($administrator);
?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox-contet">
            <?php if(Yii::$app->user->can('order-action/review-adjust-price')
            && ($administrator->isLeader() || $administrator->isDepartmentManager())): ?>
            <button class="btn btn-w-m btn-primary review-price-yes-btn" data-target="#review-price-model" data-toggle="modal">批量审核通过</button>
            <button class="btn btn-w-m btn-danger review-price-no-btn" data-target="#review-price-model" data-toggle="modal">批量审核不通过</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="tabs-container">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel-body" style="padding: 25px 20px 10px;border-bottom: 3px solid #e7eaec;">
                        <div class="page-select2-area">
                            <?php
                            $labelOptions = ['labelOptions' => ['class' => false]];
                            $form = ActiveForm::begin(['layout' => 'inline', 'method' => 'get', 'action' => ['']]); ?>
                            <div>
                                <div class="select2-options" >
                                    <?= $form->field($searchModel, 'type', $labelOptions)->widget(Select2Widget::className(), [
                                        'selectedItem' => OrderSearch::getAdjustTypes(),
                                        'placeholderId' => '0',
                                        'placeholder' => '请选择类型',
                                        'options' => ['class' => 'form-control', 'prompt' => '请选择类型'],
                                        'static' => true,
                                    ]) ?>
                                    <?= $form->field($searchModel, 'keyword')->textInput() ?>

                                    <button type="submit" class="btn btn-sm btn-primary m-t-n-xs">搜索</button>
                                </div>
                            </div>
                            <?php \yii\bootstrap\ActiveForm::end(); ?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width:80px;padding:0;vertical-align: middle;"><?= Html::checkbox('check', false,['label' => '选择','id' => 'check-all']); ?></th>
                                    <th>订单信息</th>
                                    <th>客户信息</th>
                                    <th>商品信息</th>
                                    <th>业务人员</th>
                                    <th>客服人员</th>
                                    <th>付款方式</th>
                                    <th>支付信息</th>
                                    <th>金额变动申请人</th>
                                    <th>金额变动信息</th>
                                    <th>金额变动说明</th>
                                    <th>订单状态</th>
                                    <th class="text-center">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($models as $oi => $order):
                                        $virtualModel = $order->virtualOrder;
                                        ?>
                                        <tr class="cls-opt">
                                            <td class="text-center" style="width:80px;padding:0;vertical-align: middle;">
                                                <?php if(Yii::$app->user->can('order-action/review-adjust-price')
                                                && $order->isAdjustStatusPending()
                                                && ($administrator->isLeader() || $administrator->isDepartmentManager())
                                                && $administrator->isParentDepartment($order->salesmanDepartment)): ?>
                                                <?= Html::checkbox('check', false, ['value' => $order->id]); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <p><?= $virtualModel->sn; ?></p>
                                                <p><strong><?= $order->sn; ?></strong></p>
                                                <p class="text-muted"><?= Yii::$app->formatter->asDatetime($order->created_at); ?></p>
                                                <p class="text-muted"><?= $order->getSourceAppName();?></p>
                                                <p class="text-muted"><?= $order->is_proxy ? $order->creator_name.'后台新增' : '客户自主下单'; ?></p>
                                            </td>

                                            <td>
                                                <p><a href="<?= Url::to(['user/info', 'id' => $order->user_id ])?>"><?= $order->user->name; ?></a></p>
                                                <p><?= $order->user->phone; ?></p>
                                            </td>
                                            <td>
                                                <p><strong><?= $order->product_name; ?></strong></p>
                                                <?php if ($order->district_id): ?>
                                                    <p class="text-muted"><?= $order->province_name; ?>
                                                        -<?= $order->city_name; ?>-<?= $order->district_name; ?></p>
                                                <?php else: ?>
                                                    <p class="text-muted"><?= $order->service_area; ?></p>
                                                <?php endif; ?>
                                                <p>
                                                    <?php if (!empty($order->businessSubject)):?>
                                                        <?php if ($order->businessSubject->subject_type == BusinessSubject::SUBJECT_TYPE_DISABLED):?>
                                                            <?= $order->businessSubject->company_name; ?>
                                                        <?php else:?>
                                                            <?= $order->businessSubject->region; ?>
                                                        <?php endif;?>
                                                    <?php endif;?>
                                                </p>
                                            </td>
                                            <td><?= $order->salesman_name; ?></td>
                                            <td><?= $order->customer_service_name; ?></td>

                                            <td>
                                                <?php if ($order->is_installment): ?>
                                                    <p><strong>分期付款</strong></p>
                                                <?php else: ?>
                                                    <p><strong>一次付款</strong></p>
                                                <?php endif; ?>
                                            </td>
                                            <td>
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
                                                <p>
                                                    已付金额：<?= Decimal::formatCurrentYuan($order->payment_amount); ?></p>
                                                <p <?php if ($order->getPendingPayAmount()): ?>class="text-danger"<?php else:?>class="text-primary"<?php endif; ?>>
                                                    未付金额：<?= Decimal::formatCurrentYuan($order->getPendingPayAmount()); ?></p>
                                            </td>

                                            <td><?= $order->adjustOrderPrice ? $order->adjustOrderPrice->creator_name : '--'; ?></td>
                                            <td>
                                                <p>商品原价：<?= $order->original_price; ?>元</p>
                                                <p class="text-danger">变动金额：<?= $order->adjustOrderPrice ? ($order->adjustOrderPrice->adjust_price > 0 ? '+'.$order->adjustOrderPrice->adjust_price : $order->adjustOrderPrice->adjust_price) : 0; ?>元</p>
                                                <?php if($order->coupon_remit_amount > 0):?>
                                                    <p>优惠券优惠：<?= $order->coupon_remit_amount; ?>元</p>
                                                <?php endif; ?>
                                                <?php if($order->coupon_remit_amount > 0):?>
                                                    <p>套餐优惠：<?= $order->package_remit_amount; ?>元</p>
                                                <?php endif; ?>
                                                <?php if($order->coupon_remit_amount > 0):?>
                                                    <p>微信下单优惠：<?= $order->wx_remit_amount; ?>元</p>
                                                <?php endif; ?>
                                                <p>应付金额：<?= BC::add($order->price, $order->adjustOrderPrice ? $order->adjustOrderPrice->adjust_price : 0); ?>元</p>
                                            </td>
                                            <td><?= $order->adjustOrderPrice ? $order->adjustOrderPrice->adjust_price_reason : 0; ?></td>
                                            <td>
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
                                                <?php elseif ($virtualModel->isCanceled()): ?>
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
                                                            <?php elseif($virtualModel->isUnpaid()):?>
                                                                <?= $virtualModel->getPayStatus() ?>
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
                                            <!--以下是操作部分-->
                                            <td class="text-right" style="vertical-align: middle; text-align: center;">
                                                <?php if(Yii::$app->user->can('order-action/review-adjust-price')
                                                    && $order->isAdjustStatusPending()
                                                    && ($administrator->isLeader() || $administrator->isDepartmentManager())
                                                    && $administrator->isParentDepartment($order->salesmanDepartment)): ?>
                                                    <button class="btn btn-xs btn-primary adjust-price-btn" data-id="<?= $order->id; ?>" data-original-price="<?= $order->price; ?>"
                                                            data-target="#adjust-order-price-modal" data-toggle="modal">
                                                        修改价格审核
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="13">
                                        <?=
                                        LinkPager::widget([
                                            'pagination' => $pagination
                                        ]);
                                        ?>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--批量审核start-->
<div class="modal fade" id="review-price-model" role="dialog" aria-labelledby="myModalLabel">
    <?php
    $batchAdjustForm = new \backend\models\BatchReviewAdjustPriceForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['order-action/batch-review-adjust-price'],
        'id' => 'batch-adjust-price-form',
        'validationUrl' => ['order-action/batch-review-adjust-price', 'is_validate' => 1],
        'enableAjaxValidation' => true,
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-3',
                'wrapper' => 'col-sm-8',
                'hint' => 'col-sm-12 col-sm-offset-3',
            ],
        ],
    ]);
    ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">批量审核</h4>
            </div>
            <div class="modal-body">
                确定要审核通过吗?
            </div>
            <div class="modal-footer">
                <?= Html::activeHiddenInput($batchAdjustForm,'order_id'); ?>
                <?= Html::activeHiddenInput($batchAdjustForm,'status'); ?>
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary sure-btn">确定</button>
            </div>
        </div>
    </div>
<?php
$this->registerJs(<<<JS
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
    }
    
    function checkedQty()
    {
        var _this = $('.cls-opt');
        var qty = 0;
        for(var i = 0; i < _this.length; i++)
        {
            var checkBox = _this.eq(i).find('input');
            if(checkBox.is(':checked'))
            {
               qty = qty+1;
            }
        }
        return qty;
    }
        
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
    
    $(function() 
    {
        var form = $('#batch-adjust-price-form');
        $('.review-price-yes-btn').click(function() 
        {
            var ids = checkedValues();
            var qty = checkedQty();
            var textContent = '您正在批量<span class="text-info">审核通过</span>'+qty+'个订单，要确认吗？';
            $('#batchreviewadjustpriceform-order_id').val(ids);
            $('#batchreviewadjustpriceform-status').val(2);
            form.find('.modal-body').html(textContent);
        });
        $('.review-price-no-btn').click(function() 
        {
            var ids = checkedValues();
            var qty = checkedQty();
            var textContent = '您正在批量<span class="text-danger">审核驳回</span>'+qty+'个订单，要确认吗？';
            $('#batchreviewadjustpriceform-order_id').val(ids);
            $('#batchreviewadjustpriceform-status').val(3);
            form.find('.modal-body').html(textContent);
        });
        form.on('beforeSubmit', function()
        {
            var form = $(this);
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
    })
JS
);
?>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<!--批量审核end-->

<!--修改价格start-->
<?php if (Yii::$app->user->can('order-action/review-adjust-price')): ?>
    <div class="modal fade" id="adjust-order-price-modal" role="dialog" aria-labelledby="adjust-order-price-label">
        <?php
        $adjustForm = new \backend\models\ReviewAdjustPriceForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['order-action/review-adjust-price'],
            'id' => 'adjust-price-form',
            'validationUrl' => ['order-action/review-adjust-price', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-3',
//                    'offset' => 'col-sm-offset-1',
                    'wrapper' => 'col-sm-8',
                    'hint' => 'col-sm-12 col-sm-offset-3',
                ],
            ],
        ]);
        ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="adjust-order-price-label">修改价格审核</h4>
                </div>
                <div class="spiner-example loading" style="display: block">
                    <div class="sk-spinner sk-spinner-three-bounce">
                        <div class="sk-bounce1"></div>
                        <div class="sk-bounce2"></div>
                        <div class="sk-bounce3"></div>
                    </div>
                </div>
                <div class="modal-body input_box" style="display: none">
                    <?= $form->field($adjustForm, 'origin_price')->staticControl() ?>
                    <?= $form->field($adjustForm, 'adjust_price')->textInput()->hint('需输入数字，+50为增加50元，-50为减少50元') ?>
                    <?= $form->field($adjustForm, 'price')->staticControl() ?>
                    <?= $form->field($adjustForm, 'adjust_price_reason')->textarea() ?>
                    <?= Html::activeHiddenInput($adjustForm, 'status', ['id' => 'adjust-review-status']); ?>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($adjustForm, 'order_id', ['id' => 'adjust-form_order_id']); ?>
                    <span class="text-danger warning-active"></span>
                    <button type="submit" class="btn btn-danger do-submit" value="<?= AdjustOrderPrice::STATUS_REJECT?>">审核不通过</button>
                    <button type="submit" class="btn btn-primary do-submit" value="<?= AdjustOrderPrice::STATUS_PASS?>">审核通过</button>
                </div>
            </div>
        </div>
        <?php
        $ajaxGetAdjustInfoUrl = \yii\helpers\Url::to(['order-action/get-adjust-info']);
        $this->registerJs(<<<JS
            var form = $('#adjust-price-form');
            var adjust_price_origin_price = 0;
            function changeAdjustPrice()
            {
                var adjustPrice = parseFloat(form.find('[name=adjust_price]').val());
                if(isNaN(adjustPrice)) adjustPrice = 0;
                var price = parseFloat(adjust_price_origin_price) + adjustPrice;
                form.find('.field-price .form-control-static').text(fmoney(price));
            }
            form.find('.do-submit').on('click', function(){
                form.find('#adjust-review-status').val($(this).val());
            });
            $('.adjust-price-btn').on('click', function(){
                form.find('.warning-active').text('');
                changeAdjustPrice();
                var modal = $('#adjust-order-price-modal');
                var id = $(this).attr('data-id');
                form.trigger('reset.yiiActiveForm');
                $('#adjust-form_order_id').val(id);
                adjust_price_origin_price = $(this).attr('data-original-price');
                form.find('.field-origin_price .form-control-static').text(fmoney($(this).attr('data-original-price')));
                $.get('{$ajaxGetAdjustInfoUrl}', {order_id: id}, function(rs){
                    modal.find('.input_box').show();
                    modal.find('.loading').hide();
                    if(rs['status'] === 200)
                    {
                        form.find('[name=adjust_price]').val(rs['data']['adjust_price']);
                        form.find('[name=adjust_price_reason]').val(rs['data']['adjust_price_reason']);
                        changeAdjustPrice();
                    }
                    else
                    {
                        changeAdjustPrice();
                    }
                }, 'json');
            });
            form.find('[name=adjust_price]').change(function(){
                changeAdjustPrice();
            });
            form.on('beforeSubmit', function(){
                var form = $(this);
                $.post(form.attr('action'), form.serialize(), function(rs){
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
JS
        ) ?>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>
<?php endif; ?>
<!--修改价格end-->