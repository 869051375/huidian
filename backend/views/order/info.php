<?php
/* @var $this yii\web\View */
/** @var \common\models\Order $model */
/** @var \common\models\Order $order */
/** @var \common\models\BusinessSubject $businessSubject */
/** @var \common\models\Trademark $trademarkModel */
/** @var \common\models\ExpectedProfitSettlementDetail[] $expectedProfits */
/** @var int $node_id */
/* @var $provider yii\data\ActiveDataProvider */
/* @var $dataProvider yii\data\ActiveDataProvider */

use backend\models\OrderChangeBusinessSubjectForm;
use backend\models\OrderFileSaveForm;
use backend\models\OrderFileUploadForm;
use backend\models\OrderFlowActionForm;
use backend\models\OrderRemarkForm;
use backend\models\OrderSatisfactionForm;
use backend\models\PerformanceStatisticsForm;
use common\models\BusinessSubject;
use common\models\FlowNodeAction;
use common\models\OrderBalanceRecord;
use common\models\OrderFile;
use common\models\Property;
use common\utils\BC;
use common\utils\Decimal;
use imxiangli\image\storage\ImageStorageInterface;
use imxiangli\select2\Select2Widget;
use imxiangli\upload\JQFileUpLoadWidget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\Pjax;
use zhuravljov\yii\widgets\DateTimePicker;
$order = $model;
//$thisUrl = ['order-list/all'];
//$thisLabel = '订单管理';
//if($model->isRenewal() && $model->canRenewal())
//{
//    $thisUrl = ['order-renewal/pending-renewal'];
//    $thisLabel = '续费订单管理';
//}
//$this->title = '订单详情';
//$this->params['breadcrumbs'] = [
//    ['label' => $thisLabel, 'url' => $thisUrl],
//    $this->title
//];
if(null != $model->flow)
{
    $canDisableSms = $model->flow->can_disable_sms;
}
else
{
    $canDisableSms = 0;
}
$currentNode = $model->getCurrentNode($node_id);
$url = Url::to(['order/info', 'id' => $model->id]);
/** @var \common\models\CostItem[] $cost */
$cost = $provider->query->all();
$costItemUrl = Url::to(['order-cost/create']);
$virtualModel = $order->virtualOrder;
$sign = Yii::$app->request->get('sign');
?>
    <div class="row" style="margin-top: -20px;">
        <div class="col-lg-12" style="width: 101.4%;padding: 0;margin-left: -10px;">
            <div class="ibox">
                <div class="ibox-title" style="border-top: none;height: 93px;padding: 0 25px;">
                    <h5 style="margin: 0;line-height: 93px;">【<?= $model->product_name; ?>】<?= $model->getArea() ?> </h5>
                    <div class="text-right clearfix">
                        <?php if ($order->canRefund()): ?>
                            <?php if (Yii::$app->user->can('order-action/refund')): ?>
                                <span class="btn btn-xs btn-danger refund-btn m-t-xs pull-right"
                                      data-target="#refund-order-modal"
                                      data-toggle="modal"
                                      data-id="<?= $order->id ?>" style="width: 94px;height: 34px;line-height:34px;padding: 0;margin: 30px 0 0 0;">退款</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if(Yii::$app->user->can('order-action/apply-calculate') && $order->getSurplusPerformance()): ?>
                            <?php if(!$order->orderBalanceRecord || ($order->orderBalanceRecord && $order->orderBalanceRecord->status != OrderBalanceRecord::STATUS_APPLY)): ?>
                                <span class="btn btn-xs btn-primary refund-btn m-t-xs pull-right"
                                      data-target="#apply-modal"
                                      data-toggle="modal"
                                      data-id="<?= $order->id ?>" style="width: 110px;height: 34px;line-height:34px;padding: 0;margin: 30px 10px 0 0;">申请计算业绩提成</span>
                                <a class="text-danger  pull-right" data-target="#apply-record-modal" data-toggle="modal"
                                   style="height: 93px;line-height:93px;padding: 0;margin: 0 10px 0 0;">
                                    <?php if($order->orderBalanceRecord && $order->orderBalanceRecord->status == OrderBalanceRecord::STATUS_REJECT): ?>
                                        申请计算业绩驳回
                                    <?php elseif ($order->orderBalanceRecord && $order->orderBalanceRecord->status == OrderBalanceRecord::STATUS_TRUE): ?>
                                        申请业绩已计算
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        <?php elseif(Yii::$app->user->can('order-action/apply-calculate') && !$order->getSurplusPerformance()): ?>
                            <a class="text-danger pull-right" data-target="#apply-record-modal" data-toggle="modal"
                               style="height: 93px;line-height:93px;padding: 0;margin: 0px 0 0 0;">
                                当前订单已无可计算业绩
                            </a>
                        <?php endif; ?>

                        <?php if($order->is_apply): ?>
                            <a class="text-danger pull-right" data-target="#apply-record-modal" data-toggle="modal"
                               data-id="<?= $order->id ?>" style="height: 93px;line-height:93px;padding: 0;margin: 0 10px 0 0;">申请业绩计算中，请耐心等待...</a>
                        <?php endif; ?>


                    </div>
                </div>
                <div class="ibox-content" style="padding: 10px 0;">
                    <table class="table" style="margin: 0;">
                        <thead>
                        <tr style="margin: 0;">
                            <td class="font-bold no-borders" style="width: 222px;padding-left: 44px;">订单号</td>
                            <td class="font-bold no-borders" style="width: 173px;">关联客户</td>
                            <td class="font-bold no-borders" style="width: 259px;">关联业务主体</td>
                            <td class="font-bold no-borders" style="width: 180px;">关联商机</td>
                            <td class="font-bold no-borders" style="width: 212px;">支付信息</td>
                            <td class="font-bold no-borders" style="width: 131px;">业务员</td>
                            <td class="font-bold no-borders" style="width: 131px;">客服人员</td>
                            <td class="font-bold no-borders" style="width: 222px;">服务人员</td>
                            <td class="font-bold no-borders" style="width: 222px;">财务明细编号</td>
                            <td class="font-bold no-borders" style="width: 222px;">客户满意度</td>
                            <td class="font-bold no-borders" style="width: 222px;">服务状态标记</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <!--订单号-->
                            <td class="no-borders" style="padding-left: 44px;">
                                <p>
                                    <?php if (Yii::$app->user->can('virtual-order-list/list')): ?>
                                        <a href="<?= Url::to(['virtual-order/order', 'vid' => $virtualModel->id]) ?>" target="_blank"><?= $virtualModel->sn; ?></a>
                                    <?php else: ?>
                                        <?= $virtualModel->sn; ?>
                                    <?php endif; ?>
                                </p>
                                <?= $model->sn; ?>
                            </td>

                            <!--关联客户/手机号-->
                            <td class="no-borders">
                                <a href="<?= Url::to(['user/info', 'id' => $order->user_id ])?>" target="_blank">
                                    <p><?= $order->user->name; ?>&nbsp;&nbsp;<?= $order->user->phone; ?></p>
                                </a>
                            </td>

                            <!--关联业务主体-->
                            <td class="no-borders">
                                <?php if ($order->businessSubject): ?>
                                    <a href="<?= Url::to(['business-subject/information','id'=>$order->businessSubject->id]) ?>" target="_blank">
                                        <?= $order->businessSubject->subject_type ? $order->businessSubject->region : $order->businessSubject->company_name; ?>
                                    </a>
                                <?php endif; ?>
                                <a data-target="#sel-business-subject" data-toggle="modal" class="text-info">修改</a>
                            </td>

                            <!--关联商机-->
                            <td class="no-borders">
                                <?php if(isset($order->nicheOrder)): ?>
                                    <?php foreach($order->nicheOrder as $crmOpportunity):?>
                                            <p><?= $crmOpportunity->niche->name; ?>
                                                <?php endforeach; ?>
                                <?php endif; ?>
                            </td>

                            <!--支付信息-->
                            <td class="no-borders">
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
                                <?php if(abs($order->adjust_amount) <= 0 && $order->wx_remit_amount <= 0 && $order->package_remit_amount <= 0): ?>
                                    <p class="text-danger">优惠金额：<?= Decimal::formatCurrentYuan($order->coupon_remit_amount, 2) ?></p>
                                <?php endif; ?>
                                <p>应付金额：<?= Decimal::formatCurrentYuan($order->price, 2) ?></p>
                                <?php if ($order->tax > 0): ?>
                                    <p class="text-muted">
                                        <small>(含税<?= Decimal::formatCurrentYuan($order->tax, 2) ?>)</small>
                                    </p>
                                <?php endif; ?>
                                <p>已付金额：<?= $order->payment_amount; ?></p>
                                <p class="text-danger">未付金额：<?= $order->getPendingPayAmount(); ?></p>
                                <?php if($virtualModel->isPendingPayment()): ?>
                                    <?php if($order->isAdjustStatusPending()):?>
                                        修改价格审核中
                                    <?php elseif($order->isAdjustStatusPass()):?>
                                        修改价格审核已通过
                                    <?php elseif($order->isAdjustStatusReject()):?>
                                        修改价格审核未通过
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>

                            <!--业务员-->
                            <td class="no-borders">
                                <?= $model->salesman_name; ?>
                            </td>

                            <!--客服-->
                            <td class="no-borders">
                                <?= $model->customer_service_name; ?>
                            </td>

                            <!--服务人员-->
                            <td class="no-borders">
                                <?= $model->clerk_name; ?>
                            </td>

                            <!--财务明细编号-->
                            <td class="no-borders">
                                <p><?= $order->financial_code;?></p>
                                <?php if (Yii::$app->user->can('order-action/financial-update') && $order->hasDetail()): ?>
                                    <p><a class="update-btn" href="#"
                                          data-target="#add-financial-modal" data-toggle="modal"
                                          data-id="<?= $order->id; ?>"
                                          data-whatever="财务明细编号">修改</a></p>
                                <?php endif;?>
                            </td>
                            <!--客户满意度-->
                            <td class="no-borders">
                                <p><?= $order->getSatisfactionName();?></p>
                                <?php if (Yii::$app->user->can('order-action/satisfaction') && $order->hasDetail()): ?>
                                    <p><a href="#" data-target="#satisfaction-modal" data-toggle="modal">修改</a></p>
                                <?php endif;?>
                            </td>
                            <!--服务状态标记-->
                            <td class="no-borders">
                                <p><?= $order->getServiceStatusName();?></p>
                                <?php if (Yii::$app->user->can('order-action/service-status-update') && $order->hasDetail()): ?>
                                    <p><a href="#" data-target="#service-status-modal" data-toggle="modal">修改</a></p>
                                <?php endif;?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!--审核/退款弹框start-->
        <?php if (Yii::$app->user->can('order-action/refund')): ?>
            <div class="modal fade" id="refund-order-modal" role="dialog" aria-labelledby="modal-title">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <?php
                        $refundOrderForm = new \backend\models\RefundOrderForm();
                        $form = \yii\bootstrap\ActiveForm::begin([
                            'action' => ['order-action/refund'],
                            'id' => 'refund-order-form',
                            'validationUrl' => ['order-action/refund', 'is_validate' => 1],
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
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">退款</h4>
                        </div>

                        <div class="modal-body input_box">
                            <?= $form->field($refundOrderForm, 'refund_reason')->dropDownList($refundOrderForm->getRefundReasonList(), ['id' => false]) ?>
                            <?= $form->field($refundOrderForm, 'refund_amount')->textInput(['id' => false]) ?>
                            <?= $form->field($refundOrderForm, 'is_cancel')->checkbox(['id' => 'refund_is_cancel']) ?>
                            <?= $form->field($refundOrderForm, 'refund_explain')->textarea(['maxlength' => 80, 'id' => false]) ?>
                            <?= $form->field($refundOrderForm, 'refund_remark')->textarea(['maxlength' => 80, 'id' => false]) ?>
                            <?= Html::activeHiddenInput($refundOrderForm, 'order_id', ['id' => 'refund_order_id']); ?>
                        </div>
                        <div class="modal-footer">
                            <span class="text-danger warning-active"></span>
                            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                            <button type="submit" class="btn btn-primary refund-sure-btn">确定</button>
                        </div>
                        <?php \yii\bootstrap\ActiveForm::end(); ?>
                        <?php
                        $ajaxGetRefundInfoUrl = \yii\helpers\Url::to(['order-action/get-refund-info']);
                        $this->registerJs(<<<JS
            $('.refund-audit-btn, .refund-btn').click(function(){
                var id = $(this).attr('data-id');
                var form = $('#refund-order-form');
                $.get('{$ajaxGetRefundInfoUrl}', {order_id: id}, function(rs){
                    form.find('[name=refund_amount]').val(rs['data']['is_refund_apply'] ? rs['data']['require_refund_amount'] : rs['data']['can_refund_amount']);
                    form.find('[name=refund_reason]').val(rs['data']['refund_reason']);
                    $('#refund_is_cancel').prop('checked', rs['data']['is_cancel']);
                    form.find('[name=refund_explain]').val(rs['data']['refund_explain']);
                    form.find('[name=refund_remark]').val(rs['data']['refund_remark']);
                }, 'json');
                form.find('#refund_order_id').val(id);
            });
            $('#refund-order-form').on('beforeSubmit', function(){
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
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!--审核/退款弹框end-->

        <!--选择业务主体start-->
        <div class="modal fade" id="sel-business-subject" role="dialog" aria-labelledby="business-subject-label">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <?php
                    $changeBusinessSubjectForm = new OrderChangeBusinessSubjectForm();
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'action' => ['change-business-subject'],
                        'id' => 'change-administrator-form',
                    ]); ?>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="confirm-receive-label">选择业务主体</h4>
                    </div>
                    <div class="modal-body">
                        <?= $form->field($changeBusinessSubjectForm, 'business_subject_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                            'attribute' => 'business_subject_id',
                            'serverUrl' => \yii\helpers\Url::to(['business-subject/list', 'customer_id' => $order->user->id]),
                            'itemsName' => 'data',
                            'selectedItem' => [],
                            'options' => ['prompt' => '选择业务主体'],
                            'placeholderId' => '0',
                            'placeholder' => '选择业务主体',
                            'eventSelect' => new JsExpression("
                ")
                        ])?>
                        <?= \yii\bootstrap\Html::activeHiddenInput($changeBusinessSubjectForm,'order_id',['value'=>$order->id]) ?>
                    </div>
                    <div class="modal-footer">
                        <span class="text-danger warning-active"></span>
                        <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary sure-btn">确定</button>
                    </div>
                    <?php \yii\bootstrap\ActiveForm::end(); ?>
                    <?php $this->registerJs(<<<JS
            $.fn.select2.defaults.set('width', '80%');
            $('#change-administrator-form').on('beforeSubmit', function(){
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
                    );?>
                </div>
            </div>
        </div>
        <!--选择业务主体end-->

        <!--财务明细编号start-->
        <div class="modal fade" id="add-financial-modal" role="dialog"
             aria-labelledby="modal-title">
            <?php
            $orderFinancialModel = new \backend\models\OrderFinancialForm();
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['order-action/financial-update'],
                'validationUrl' => ['order-action/ajax-financial-validation'],
                'enableAjaxValidation' => true,
                'id' => 'order-financial-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-3',
                        'offset' => 'col-sm-offset-1',
                        'wrapper' => 'col-sm-8',
                    ],
                ],
            ]); ?>
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">财务明细编号</h4>
                    </div>
                    <div class="modal-body input_box">
                        <?= $form->field($orderFinancialModel, 'financial_code')->textInput() ?>
                        <div class="col-sm-offset-3 col-sm-8 text-danger"> 注意：请输入字母+数字的组合，例如C1234</div>
                    </div>
                    <?= \yii\helpers\Html::activeHiddenInput($orderFinancialModel, 'order_id', ['value' => $order->id])?>
                    <div class="modal-footer">
                        <span class="text-danger warning-active"></span>
                        <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                    </div>
                </div>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
        </div>
        <?php
        $ajaxFinancialInfoUrl = \yii\helpers\Url::to(['order-action/ajax-financial-info', 'id' => '__id__']);
        $ajaxFinancialUpdateUrl = \yii\helpers\Url::to(['order-action/financial-update', 'id' => '__id__']);
        $this->registerJs(<<<JS
                $('#add-financial-modal').on('show.bs.modal', function (event) {
                    $('#orderfinancialform-form').trigger('reset.yiiActiveForm');
                    var button = $(event.relatedTarget);
                    var recipient = button.data('whatever');
                    var modal = $(this);
                    modal.find('.modal-title').text(recipient);
                });
                $('.cancel-btn').on('click',function(){
                    $('.warning-active').html('');
                });
                $('.update-btn').on('click',function(){
                    var id = $(this).attr('data-id');
                    var updateAction = '{$ajaxFinancialUpdateUrl}';
                    $('.modal form').attr('action', updateAction.replace('__id__', id));
                    $.get('{$ajaxFinancialInfoUrl}'.replace('__id__', id),function(rs){
                        if(rs.status==200){
                            $('#orderfinancialform-financial_code').val(rs.model.financial_code);
                        }
                    },'json');
                });
JS
        );
        ?>
        <!--财务明细编号end-->

        <!--客户满意度start-->
        <div class="modal fade" id="satisfaction-modal" role="dialog"
             aria-labelledby="modal-title">
            <?php
            $orderSatisfactionForm = new OrderSatisfactionForm();
            $orderSatisfactionForm->is_satisfaction = $order->is_satisfaction;
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['order-action/satisfaction'],
                'validationUrl' => ['order-action/satisfaction', 'is_validate' => 1],
                'enableAjaxValidation' => true,
                'id' => 'order-satisfaction-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-3',
                        'offset' => 'col-sm-offset-1',
                        'wrapper' => 'col-sm-8',
                    ],
                ],
            ]); ?>
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">客户满意度调研</h4>
                    </div>
                    <div class="modal-body input_box">
                        <?= $form->field($orderSatisfactionForm, 'is_satisfaction')->dropDownList(\common\models\Order::getSatisfaction(), ['prompt' => '请选择客户满意度']) ?>
                    </div>
                    <?= \yii\helpers\Html::activeHiddenInput($orderSatisfactionForm, 'order_id', ['value' => $order->id])?>
                    <div class="modal-footer">
                        <span class="text-danger warning-active"></span>
                        <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                    </div>
                </div>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
            <?php
            $this->registerJs(<<<JS
            $('#order-satisfaction-form').on('beforeSubmit', function(){
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
            );
            ?>
        </div>
        <!--客户满意度end-->


        <!--服务状态标记start-->
        <div class="modal fade" id="service-status-modal" role="dialog" aria-labelledby="modal-title">
            <?php
            $orderServiceStatus = new \common\models\Order();
            $orderServiceStatus->service_status = $order->service_status;
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['order-action/service-status-update'],
                'validationUrl' => ['order-action/service-status-update', 'is_validate' => 1],
                'enableAjaxValidation' => true,
                'id' => 'order-service-status-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-3',
                        'offset' => 'col-sm-offset-1',
                        'wrapper' => 'col-sm-8',
                    ],
                ],
            ]); ?>
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">订单服务状态标记</h4>
                    </div>
                    <div class="modal-body input_box">
                        <?= $form->field($orderServiceStatus, 'service_status')->dropDownList(\common\models\Order::getServiceStatus(), ['prompt' => '请选择服务状态标记']) ?>
                    </div>
                    <?= \yii\helpers\Html::activeHiddenInput($orderServiceStatus, 'order_id', ['value' => $order->id])?>
                    <div class="modal-footer">
                        <span class="text-danger warning-active"></span>
                        <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                    </div>
                </div>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
            <?php
            $this->registerJs(<<<JS
                var num = 0;
            	$("#service-status-modal .add-sure-btn").click(function(){
            		num++;
            		if(num == 1){
            			var form = $('#order-service-status-form');
		                $.post(form.attr('action'), form.serialize(), function(rs){
		                	$("#service-status-modal .add-sure-btn").attr('disabled','disabled');
		                    if(rs.status === 200)
		                    {
								form.trigger('reset.yiiActiveForm');
								window.location.reload();
		                    }
		                    else
		                    {
		                    	num = 0;
		                      	$("#service-status-modal .add-sure-btn").removeAttr("disabled");
		                        form.find('.warning-active').text(rs.message);
		                    }
		                }, 'json');
		                return false;
            		}
            		console.log(num)
            	})
JS
            );
            ?>
        </div>
        <!--服务状态标记end-->


        <!--订单申请计算业绩start-->
        <div class="modal fade" id="apply-modal" role="dialog"
             aria-labelledby="modal-title">
            <?php
            $orderApplyPerformanceForm = new \backend\models\OrderApplyPerformanceForm();
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['order-action/apply-performance'],
                'validationUrl' => ['order-action/apply-performance', 'is_validate' => 1],
                'enableAjaxValidation' => true,
                'id' => 'apply-performance-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-3',
                        'offset' => 'col-sm-offset-1',
                        'wrapper' => 'col-sm-8',
                    ],
                ],
            ]); ?>
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">申请计算业绩提成<span class="text-danger">（该操作一旦申请，即不可撤销）</span></h4>
                    </div>
                    <div class="modal-body input_box">
                        确定要申请计算业绩提成吗？
                    </div>
                    <?= \yii\helpers\Html::activeHiddenInput($orderApplyPerformanceForm, 'order_id', ['value' => $order->id])?>
                    <div class="modal-footer">
                        <span class="text-danger warning-active"></span>
                        <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                    </div>
                </div>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
            <?php
            $this->registerJs(<<<JS
            $('#apply-performance-form').on('beforeSubmit', function(){
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
            );
            ?>
        </div>
        <!--订单申请计算业绩end-->

        <!--订单申请计算业绩记录start-->
        <div class="modal fade" id="apply-record-modal" role="dialog" aria-labelledby="modal-title">
            <?php Pjax::begin(); ?>
            <?= $this->render('apply-record',['dataProvider' => $dataProvider]) ?>
            <?php Pjax::end(); ?>
        </div>
        <!--订单申请计算业绩记录end-->

        <!--订单流程节点start-->
        <div class="col-lg-12">
            <div class="ibox">
                <?php if ($model->isRenewal() && $model->canRenewal()):?>
                    <?php if ($model->isRenewalWarning() && $model->isPendingRenewal()):;?>
                        <div class="ibox-title">
                            <?php if ($model->end_service_cycle > 0):?>
                                <?php if ($model->end_service_cycle > time()):?>
                                    <?php if (abs($model->renewalDate() == 0)):?>
                                        <p class="bg-primary">服务<span class="text-danger" style="font-size:35px;">今日</span>到期，请及时跟进客户续费，服务完成后及时点击【服务已完成】</p>
                                    <?php else:?>
                                        <p class="bg-primary">距离服务到期时间还剩<span class="text-danger" style="font-size:35px;"><?= abs($model->renewalDate())?></span>天，请及时跟进客户续费，服务完成后及时点击【服务已完成】</p>
                                    <?php endif;?>
                                <?php else:?>
                                    <p class="bg-primary">距离服务周期截止日期已过期<span class="text-danger" style="font-size:35px;"><?= abs($model->renewalDate())?></span>天，请继续跟进客户续费，服务完成后及时点击【服务已完成】</p>
                                <?php endif;?>
                            <?php endif;?>
                        </div>
                    <?php endif;?>
                <?php endif;?>
                <div class="ibox-title">
                    <h5> 订单流程进度 </h5>
                </div>

                <?php if ($model->flow): ?>
                    <div class="ibox-content text-center">
                        <?php
                        $count = count($model->flow->nodes);
                        foreach ($model->flow->nodes as $key => $node):?>
                            <?php if (!$model->flowIsFinish() && !$model->isBreakService()): ?>
                                <a <?php if (Yii::$app->user->can('order-action/do-flow-action')): ?> href="<?= Url::to(['info', 'id' => $model->id, 'node_id' => $node->id]) ?>" <?php endif;?>
                                        class="btn btn-sm <?php if ($currentNode->id == $node->id): ?>btn-primary<?php elseif ($currentNode->sequence > $node->sequence): ?>btn-success<?php else: ?>btn-default<?php endif; ?>"><?= $node->name; ?></a>
                            <?php else: ?>
                                <span class="btn btn-sm <?php if ($currentNode->id == $node->id): ?>btn-primary<?php elseif ($currentNode->sequence > $node->sequence): ?>btn-success<?php else: ?>btn-default<?php endif; ?> disabled"
                                      style="cursor: default;"><?= $node->name; ?></span>
                            <?php endif; ?>
                            <?php if ($key + 1 < $count): ?>
                                <span class="btn btn-link btn-sm disabled" style="cursor: default;">&gt;</span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php
                //2017-11-09 待分配、待服务不能操作流程节点
                if(!$order->isPendingService()&&!$order->isPendingAllot()): ?>
                    <div class="ibox-footer text-center">
                        <?php if($model->isBreakService()):?>
                            <p class="lead">订单已取消</p>
                            <p>退款金额：<?= Decimal::formatCurrentYuan($model->refund_amount)?></p>
                            <p>退款说明：<?= $model->getRefundReasonText() ?></p>
                            <p>备注：<?= $model->refund_remark ?></p>
                        <?php elseif($model->flow): ?>
                            <?php if ($currentNode): ?>
                                <p class="lead">
                                    <?php $hint = $model->getHintOperator($currentNode) ?>
                                    <?= $hint['content'] ?>
                                </p>
                                <?php if (!$model->flowIsFinish()): ?>
                                    <?php foreach ($currentNode->actions as $action): ?>
                                        <?php if (Yii::$app->user->can('order-action/do-flow-action')): ?>
                                            <span class="btn-restart-<?= $action->id?> btn btn-sm <?php if ($action->isStay()): ?>btn-danger<?php else: ?>btn-success<?php endif; ?><?php if($action->isTypeUpload()):?> upload-file-confirm-btn<?php endif; ?>"
                                                  data-target="#flow-action-modal-<?= $action->id ?>"
                                                  data-toggle="modal"><?= $action->action_label ?></span>
                                        <?php endif;?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!--订单流程节点end-->

        <!--订单详情页(订单记录、短信记录、文件上传)start-->
        <div class="col-sm-12">
            <div class="ibox">
                <div class="tabs-container">
                    <div class="row">
                        <div class="col-lg-10">
                            <ul class="nav nav-tabs">
                                <li class="<?php if(empty($sign)): ?>active<?php endif; ?>"><a data-toggle="tab" href="#tab-order-info" aria-expanded="true">详细信息</a></li>
                                <?php if($model->is_trademark):  ?>
                                    <li class=""><a data-toggle="tab" href="#tab-trademark">商标信息</i></a></li>
                                <?php endif; ?>
                                <li class=""><a data-toggle="tab" href="#tab-order-record" aria-expanded="false">订单记录</a></li>
                                <li class=""><a data-toggle="tab" href="#tab-order-sms" aria-expanded="false">短信记录</a></li>
                                <li class=""><a data-toggle="tab" href="#tab-order-file" aria-expanded="false">文件资料</a></li>
                                <li class=""><a data-toggle="tab" href="#tab-order-remark" aria-expanded="false">备注信息</a></li>
                                <?php if (Yii::$app->user->can('cost/list')): ?>
                                    <li class=""><a data-toggle="tab" href="#tab-order-cost" aria-expanded="false">成本管理</a></li>
                                <?php endif; ?>
                                <?php if (Yii::$app->user->can('performance/list')): ?>
                                    <li <?php if($sign):?>class="active"<?php endif; ?>><a data-toggle="tab" href="#tab-order-performance" aria-expanded="true" id="performance">实际利润和提成管理</a></li>
                                <?php endif; ?>

                            </ul>
                        </div>
                        <div class="col-lg-2" style="text-align: right;">
                            <?php if (Yii::$app->user->can('order-action/do-flow-action') || Yii::$app->user->can('order-action/upload')): ?>
                                <button id="add-upload" class="btn btn-sm btn-primary btn-upload" data-target="#order-file-upload-modal"
                                        data-toggle="modal">文件上传</button>
                            <?php endif;?>
                            <?php if (Yii::$app->user->can('order-action/add-remark')): ?>
                                <button id="add-remark" class="btn btn-sm btn-primary btn-upload" data-target="#order-remark-modal"
                                        data-toggle="modal">添加备注</button>
                            <?php endif;?>
                        </div>
                    </div>

                    <div class="tab-content">
                        <!--详细信息start-->
                        <div id="tab-order-info" class="tab-pane <?php if(empty($sign)): ?>active<?php endif; ?>">
                            <div class="panel-body">
                                <div class="no-borders no-paddings" style="padding:0;">
                                    <div style="overflow: hidden;">
                                        <div class="border-bottom p-sm">
                                            <div class="font-bold "><i class="border-left-color m-r-sm"></i>订单基本信息</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">订单号：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->sn; ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">下单方式：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->is_proxy ? $order->creator_name.'后台新增' : '客户自主下单'; ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">订单商品：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->product_name; ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md" >商品金额：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= Decimal::formatCurrentYuan($order->original_price, 2) ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">变动金额：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;">
                                                        <?php if(abs($order->adjust_amount) > 0): ?>
                                                            <p><?= Decimal::formatCurrentYuan($order->adjust_amount, 2, [], [], true) ?></p>
                                                        <?php else: ?>
                                                            --
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">已付金额：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;">
                                                        <?= $order->payment_amount ? Decimal::formatCurrentYuan($order->payment_amount, 2) : '--'; ?>
                                                    </div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">嘟嘟妹：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->supervisor_name ? $order->supervisor_name: '--'; ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">客服人员：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->customer_service_name ? $order->customer_service_name : '--'; ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">下单人员：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->creator_name; ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">首款支付时间：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->first_payment_time ? date('Y-m-d H:i:s',$order->first_payment_time) : '--'; ?></div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-offset-1 col-md-3 p-md">开始服务时间：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->begin_service_time ? date('Y-m-d H:i:s',$order->begin_service_time) : '--'; ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">订单来源：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->getSourceAppName(); ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">付款方式：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->is_installment ? '分期付款': '一次付款'; ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">服务地区：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->getArea(); ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">优惠金额：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;">
                                                        <?php if(abs($order->adjust_amount) <= 0 && $order->wx_remit_amount <= 0 && $order->package_remit_amount <= 0): ?>
                                                            <?= Decimal::formatCurrentYuan($order->coupon_remit_amount, 2) ?>
                                                        <?php else: ?>
                                                            --
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">应付金额：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= Decimal::formatCurrentYuan($order->price, 2) ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">未付金额：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;">
                                                        <?php if ($virtualModel->isUnpaid() || $virtualModel->isPendingPayment()): ?>
                                                            <?= $virtualModel->getPendingPayAmount(); ?>
                                                        <?php else: ?>
                                                            --
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">业务人员：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->salesman_name ? $order->salesman_name: '--'; ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">服务人员：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->clerk_name ? $order->clerk_name : '--'; ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">下单时间：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->created_at ? date('Y-m-d H:i:s',$order->created_at) : '--'; ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-3 p-md">全款完成时间：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;">
                                                        <?php if($virtualModel->isAlreadyPayment()): ?>
                                                            <?= $virtualModel->payment_time ? date('Y-m-d H:i:s',$virtualModel->payment_time) : '--'; ?>
                                                        <?php else: ?>
                                                            --
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-offset-1 col-md-3 p-md">服务完成时间：</div>
                                                    <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $order->complete_service_time ? date('Y-m-d H:i:s',$order->complete_service_time) : '--'; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="border-bottom">
                                            <div class="ibox-title">
                                                <p><i class="border-left-color m-r-sm"></i>订单服务信息&nbsp;&nbsp;
                                                    <?php if($model->isRenewal() && $model->canRenewal()):?>
                                                        <a class="service_cycle_edit" data-target="#order-edit-modal" data-toggle="modal">编辑</a>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="row cycle_list">
                                            <div class="col-md-6">
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-4 p-md">服务周期：</div>
                                                    <div class="col-md-7 p-md" style="margin-left: -45px;">
                                                        <?php //$order->getServiceDays()? $order->getServiceDays(): '--'; ?>
                                                        <?= $order->service_cycle > 0 ? $order->service_cycle .'个月': '--'; ?>
                                                    </div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-4 p-md">服务结束时间：</div>
                                                    <div class="col-md-7 p-md" style="margin-left: -45px;"><?= $order->end_service_cycle ? date('Y-m-d',$order->end_service_cycle):'--'; ?></div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-offset-1 col-md-4 p-md">预估服务结束时间：</div>
                                                    <div class="col-md-7 p-md" style="margin-left: -45px;"><?= $order->estimate_service_time ? date('Y-m-d',$order->estimate_service_time):'--'; ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-4 p-md">服务开始时间：</div>
                                                    <div class="col-md-7 p-md" style="margin-left: -45px;"><?= $order->begin_service_cycle ? date('Y-m-d',$order->begin_service_cycle):'--'; ?></div>
                                                </div>
                                                <div class="row hr-line-bottom-dashed">
                                                    <div class="col-md-offset-1 col-md-4 p-md">续费报警开始时间：</div>
                                                    <div class="col-md-7 p-md" style="margin-left: -45px;"><?= $order->renewal_warn_time ? date('Y-m-d',$order->renewal_warn_time):'--'; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        $this->registerJs(<<<JS
//                                              $('.service_cycle_edit').click(function() 
//                                              {
//                                                  $('.cycle_edit').removeClass('hide').show();
//                                                  $('.cycle_list').hide();
//                                              })
JS
                                        );

                                        ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--详细信息end-->

                        <!--商标信息start-->
                        <?php if($model->is_trademark):  ?>
                            <div id="tab-trademark" class="tab-pane">
                                <div class="panel-body">
                                    <div class="ibox">
                                        <div class="ibox-content">
                                            <?php
                                            $form = \yii\bootstrap\ActiveForm::begin([
                                                'action' => ['order/trademark-create'],
                                                'validationUrl' => ['order/validation-trademark'],
                                                'enableAjaxValidation' => true,
                                                'id' => 'trademark-form',
                                                'layout' => 'horizontal',
                                                'fieldConfig' => [
                                                    'horizontalCssClasses' => [
                                                        'label' => 'col-md-2',
                                                        'offset' => 'col-md-offset-2',
                                                        'wrapper' => 'col-md-4',
                                                        'hint' => 'col-md-6',
                                                    ],
                                                ]]);
                                            ?>
                                            <?= $form->field($trademarkModel, 'name')->textInput(['id'=>'name'])->label('商标名称'); ?>
                                            <?= $form->field($trademarkModel, 'description')->textInput(['id'=>'description'])->label('商标说明'); ?>
                                            <div class="page-select2-area">
                                                <?php
                                                echo $form->field($trademarkModel, 'category_id', [
                                                    'template' => "{label}\n<div class='col-sm-4'><div class='row'><div class='col-sm-4'>{input}</div></div>\n{hint}\n{error}</div>",
                                                ])->widget(Select2Widget::className(), [
                                                    'model' => $trademarkModel,
                                                    'attribute' => 'category_id',
                                                    'serverUrl' => \yii\helpers\Url::to(['order/ajax-trademark-category']),
                                                    'itemsName' => 'provinces',
                                                    'selectedItem' => $trademarkModel->category_id ? [$trademarkModel->category_id => $trademarkModel->category_name] : [],
                                                    'options' => ['class' => 'form-control', 'prompt'=>'请选择类别'],
                                                    'placeholder' => '请选择类别',
                                                ])->label('商标类别');
                                                ?>
                                            </div>
                                            <?php $field = $form->field($trademarkModel, 'image')->hiddenInput(['id' => 'trademark_image_key']);
                                            $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                                                    'buttonTitle' => '上传图片',
                                                    'name' => 'file',
                                                    'serverUrl' => ['upload'],
                                                    'formData' =>[
                                                        Yii::$app->request->csrfParam => Yii::$app->request->csrfToken,
                                                    ],
                                                    'done' => new \yii\web\JsExpression('function (e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-banner_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#img").empty().append($("<img class=\"thumbnail margin0\" />").attr("src", file.thumbnailUrl));
                                                $("#trademark_image_key").val(file.key);
                                                $("#trademark_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }')
                                                ])
                                            ?>
                                            <?= $field->label('上传商标图样') ?>
                                            <div class="form-group">
                                                <label class="control-label col-sm-2"></label>
                                                <div class="col-sm-8">
                                                    <div id="img">
                                                        <?php if ($trademarkModel->image): ?>
                                                            <img class="thumbnail margin0"
                                                                 src="<?= $trademarkModel->getImageUrl(300, 202) ?>"/>
                                                            <button class="btn btn-xs btn-danger delete-image" data-id="<?= $trademarkModel ? $trademarkModel->id : ''; ?>" type="button">删除图片</button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?= $form->field($trademarkModel, 'apply_no')->textInput(['id'=>'apply_no'])->label('申请号'); ?>
                                            <?= \yii\bootstrap\Html::activeHiddenInput($trademarkModel, 'order_id',['value'=>$model->id]) ?>
                                            <?= \yii\bootstrap\Html::activeHiddenInput($trademarkModel, 'id',['value'=>$trademarkModel->id]) ?>
                                            <div class="form-group">
                                                <div class="col-sm-4 col-sm-offset-2">
                                                    <button class="btn btn-primary" type="submit">保存</button>
                                                </div>
                                            </div>
                                            <?php \yii\bootstrap\ActiveForm::end(); ?>
                                        </div>
                                        <?php
                                        $delUrl = \yii\helpers\Url::to(['trademark-delete']);
                                        $this->registerJs(<<<JS
           $('#trademark-category_id').css('width', '355%');
            $('.delete-image').click(function(){
                var trademark_id =  $(this).attr('data-id');
                $.post('{$delUrl}',{trademark_id:trademark_id},function(rs){
                    if(rs.status==200)
                    {
                        $('#img').empty();
                    }else{
                        $('#img').empty().text(rs.message);
                    }
                },'json')
            })
JS
                                        ) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!--商标信息end-->

                        <!--订单记录start-->
                        <div id="tab-order-record" class="tab-pane">
                            <div class="panel-body">
                                <table class="table table1 table-order">
                                    <thead>
                                    <tr>
                                        <th width="15%">时间</th>
                                        <th width="20%">状态</th>
                                        <th width="50%">备注</th>
                                        <th width="15%">操作人</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <!--订单记录表数据start-->
                                    <?php if(!empty($model->orderRecords)): ?>
                                        <?php foreach ($model->orderRecords as $orderRecord):?>
                                            <?php if($orderRecord->order_flow_record_id > 0):?>
                                                <!--此处循环流程操作数据-->
                                                <?php foreach ($orderRecord->flowRecords as $flowRecord): ?>
                                                    <tr>
                                                        <td><?= Yii::$app->formatter->asDatetime($flowRecord->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                                        <td><?= $flowRecord->flow_action_name ?></td>
                                                        <td>
                                                            <?php if ($flowRecord->action->isTypeDate()): ?>
                                                                <p><?= $flowRecord->action->action_hint ?>
                                                                    : <?= $flowRecord->input_date; ?></p>
                                                            <?php endif; ?>
                                                            <?php foreach ($flowRecord->getInputText() as $textItem): ?>
                                                                <p><?= $textItem['label'] ?>
                                                                    : <?= $textItem['text'] ?></p>
                                                            <?php endforeach; ?>
                                                            <?php if($flowRecord->orderFile):?>
                                                                <?php $files = $flowRecord->orderFile->getFiles()?>
                                                                <?php foreach($files as $file):?>
                                                                    <p class="text-primary"><a href="<?= OrderFile::getUrl($file['key'])?>" target="_blank" download="<?= $file['name']?>"><?= $file['name']?></a></p>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= $flowRecord->creator_id ? $flowRecord->creator_name : '客户' ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else:?>
                                                <tr>
                                                    <td><?= Yii::$app->formatter->asDatetime($orderRecord->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                                    <td><?= $orderRecord->title?></td>
                                                    <td>
                                                        <?php if($orderRecord->file_id >0):?>
                                                            <?php $files = $orderRecord->orderFile->getFiles()?>
                                                            <p><?= $orderRecord->orderFile->remark; ?></p>
                                                            <?php foreach($files as $file):?>
                                                                <p class="text-primary"><a href="<?= OrderFile::getUrl($file['key'])?>" target="_blank" download="<?= $file['name']?>"><?= $file['name']?></a></p>
                                                            <?php endforeach; ?>
                                                        <?php elseif ($orderRecord->receipt_id > 0):?>
                                                            <?php $files = $orderRecord->receipt->getFiles();
                                                            /** @var ImageStorageInterface $imageStorage */
                                                            $imageStorage = \Yii::$app->get('imageStorage');
                                                            ?>
                                                            <p><?= $orderRecord->remark; ?></p>
                                                            <?php foreach($files as $file):?>
                                                                <span class="review-btn text-primary" data-target="#image-modal" data-toggle="modal" data-image="<?= $imageStorage->getImageUrl($file)?>"><a href="javascript:">查看回款截图</a></span>
                                                            <?php endforeach; ?>
                                                        <?php else:?>
                                                            <?= $orderRecord->remark?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if(null != $orderRecord->creator_name):?>
                                                            <?= $orderRecord->creator_name ?>
                                                        <?php else:?>
                                                            客户
                                                        <?php endif;?>
                                                    </td>
                                                </tr>
                                            <?php endif;?>
                                        <?php endforeach;?>
                                    <?php endif;?>
                                    <!--订单记录表数据end-->
                                    </tbody>
                                </table>
                                <div class="more-end-btn">
                                    <p>查看更多</p>
                                </div>
                            </div>
                        </div>
                        <!--订单记录end-->

                        <!--短信记录start-->
                        <div id="tab-order-sms" class="tab-pane">
                            <div class="panel-body">
                                <table class="table table-message table1">
                                    <thead>
                                    <tr>
                                        <th width="15%">时间</th>
                                        <th width="15%">手机号</th>
                                        <th width="55%">短信内容</th>
                                        <th width="15%">操作人</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if($model->orderSms): ?>
                                        <?php foreach ($model->orderSms as $orderSms): ?>
                                            <tr>
                                                <td><?= Yii::$app->formatter->asDatetime($orderSms->created_at, 'yyyy-MM-dd HH:mm')?></td>
                                                <td><?= $orderSms->phone; ?></td>
                                                <td><?= $orderSms->content; ?></td>
                                                <td><?= $orderSms->creator_name; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td>暂无数据</td>
                                            <td></td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                                <div class="more-end-btn">
                                    <p>查看更多</p>
                                </div>
                            </div>
                        </div>
                        <!--短信记录end-->

                        <!--文件资料start-->
                        <div id="tab-order-file" class="tab-pane">
                            <div class="panel-body">
                                <table class="table table-file table1">
                                    <thead>
                                    <tr>
                                        <th width="13%">上传时间</th>
                                        <th width="7%">前台可见</th>
                                        <th width="50%">备注信息</th>
                                        <th width="20%">文件内容</th>
                                        <th width="10%">操作人</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if($model->orderFiles): ?>
                                        <?php foreach ($model->orderFiles as $orderFile): ?>
                                            <tr>
                                                <td><?= Yii::$app->formatter->asDatetime($orderFile->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                                <td><?php if($orderFile->is_internal):?>不可见<?php else:?>可见<?php endif;?></td>
                                                <td><?= $orderFile->remark; ?></td>
                                                <td>
                                                    <?php $files = $orderFile->getFiles()?>
                                                    <?php foreach($files as $file):?>
                                                        <p class="text-primary"><a href="<?= OrderFile::getUrl($file['key'])?>" target="_blank" download="<?= $file['name']?>"><?= $file['name']?></a></p>
                                                    <?php endforeach; ?>
                                                </td>
                                                <td>
                                                    <?php if($orderFile->isCustomer()): ?>
                                                        客户上传
                                                    <?php else: ?>
                                                        <?= $orderFile->creator_name; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td>暂无数据</td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                                <div class="more-end-btn">
                                    <p>查看更多</p>
                                </div>
                            </div>
                        </div>
                        <!--文件资料end-->

                        <!--备注信息start-->
                        <div id="tab-order-remark" class="tab-pane">
                            <div class="panel-body">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th width="15%">时间</th>
                                        <th width="70%">备注信息</th>
                                        <th width="15%">操作人</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if($model->orderRemarks): ?>
                                        <?php foreach ($model->orderRemarks as $orderRemark): ?>
                                            <tr>
                                                <td><?= Yii::$app->formatter->asDatetime($orderRemark->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                                <td><?= $orderRemark->remark; ?></td>
                                                <td><?= $orderRemark->creator_name; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td></td>
                                            <td>暂无数据</td>
                                            <td></td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--备注信息end-->

                        <!--成本信息start-->
                        <div id="tab-order-cost" class="tab-pane">
                            <div class="panel-body" style="padding-top: 0;">
                                <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>订单业绩提点月</b>：<span class="text-info">(订单业绩提点月根据预计利润首次计算时间自动生成)</span></p>
                                <?= $order->settlement_month ? mb_substr($order->settlement_month,0,4).'年'.mb_substr($order->settlement_month,4,2).'月' : ''; ?>
                                <?php if(Yii::$app->user->can('order-action/settlement-month')): ?>
                                    <a class="btn text-info settlement-month-btn" data-target="#settlement-month" data-toggle="modal">修改</a>
                                    <!--订单业绩提点月弹窗开始-->
                                    <div class="modal fade" id="settlement-month" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                        <?php
                                        $settlementMonthForm = new \backend\models\SettlementMonthForm();
                                        $settlementMonthForm->settlement_month = date('Ym');
                                        $form = \yii\bootstrap\ActiveForm::begin([
                                            'action' => ['order-action/settlement-month'],
                                            'validationUrl' => ['order-action/settlement-month','is_validate' => 1],
                                            'enableAjaxValidation' => true,
                                            'id' => 'settlement-month-form',
                                            'layout' => 'horizontal',
                                            'fieldConfig' => [
                                                'horizontalCssClasses' => [
                                                    'label' => 'col-sm-4',
                                                    'offset' => 'col-sm-offset-2',
                                                    'wrapper' => 'col-sm-3',
                                                    'hint' => 'col-sm-3'
                                                ],
                                            ],
                                        ]); ?>
                                        ?>
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title">订单业绩提点月</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?= $form->field($settlementMonthForm, 'settlement_month')->textInput()->hint('例：201711') ?>
                                                    <?= Html::activeHiddenInput($settlementMonthForm, 'order_id',['value' => $order->id]) ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <span class="text-danger warning-active"></span>
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                                    <button type="submit" class="btn btn-primary">保存</button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        $this->registerJs(<<<JS
                                        $(function() 
                                        {
                                            var form = $('#settlement-month-form');
                                            $('.settlement-month-btn').click(function()
                                            {
                                                form.find('.warning-active').empty();
                                            });
                                            //订单业绩提点月
                                            form.on('beforeSubmit', function()
                                            {
                                                $.post(form.attr('action'), form.serialize(), function(rs)
                                                {
                                                    if(rs.status !== 200)
                                                    {
                                                        form.find('.warning-active').text(rs.message);
                                                    }else{
                                                        window.location.reload();
                                                    }
                                                },'json');
                                                return false;
                                            });
                                        })
JS
                                        );
                                        ?>
                                        <?php \yii\bootstrap\ActiveForm::end(); ?>
                                    </div>
                                    <!--订单业绩提点月弹窗结束-->
                                <?php endif; ?>
                                <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>预计成本管理</b>：<span class="text-info">(成本一旦增加后，不允许删除，若输入错误，可再增加一笔成本进行回冲，如多输入了一笔刻章费，300元，须再增加一笔刻章费，-300元，备注里务必说明情况)</span></p>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead >
                                        <tr>
                                            <th class="text-center" style="width: 304px;">增加时间</th>
                                            <th class="text-center" style="width: 265px;">成本类型</th>
                                            <th class="text-center" style="width: 265px;">成本名称</th>
                                            <th class="text-center" style="width: 265px;">成本金额</th>
                                            <th class="text-center" style="width: 265px;">备注</th>
                                            <th class="text-center" style="width: 224px;">操作人</th>
                                        </tr>
                                        </thead>
                                        <tbody id="zhao">  
                                        <?php
                                        $cost_price = 0;
                                        foreach ($order->orderExpectedCost as $expectedCost):
                                            $cost_price  += floatval($expectedCost->cost_price);
                                            ?>
                                            <tr style="background: none;">
                                                <td class="text-center"><?= date('Y-m-d H:i:s',$expectedCost->created_at); ?></td>
                                                <td class="text-center"><?= $expectedCost->getTypeName(); ?></td>
                                                <td class="text-center"><?= $expectedCost->cost_name; ?></td>
                                                <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($expectedCost->cost_price); ?></td>
                                                <td class="text-center"><?= $expectedCost->remark; ?></td>
                                                <td class="text-center"><?= $expectedCost->creator_name; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                         </tbody>
                                        <tr style="background: none;">
                                            <td colspan="6" class="text-right">
                                                <span class="text-danger cost-amount" style="margin-right: 10px" data-cost = "<?= Decimal::formatYenCurrentNoWrap($cost_price); ?>">预计成本金额：<?= Decimal::formatYenCurrentNoWrap($cost_price); ?></span>
                                                <span class="text-danger profit-amount" style="margin-right: 10px" data-profit = "<?= Decimal::formatYenCurrentNoWrap(BC::sub($order->price,$cost_price)); ?>">预计利润金额：<?= Decimal::formatYenCurrentNoWrap(BC::sub($order->price,$cost_price)); ?></span>
                                                <span class="text-danger surplus-profit" data-surplus = "<?= Decimal::formatYenCurrentNoWrap(BC::sub(BC::sub($order->price,$cost_price),$order->getExpectedProfit())); ?>">剩余可计算预计利润：<?= Decimal::formatYenCurrentNoWrap(BC::sub(BC::sub($order->price,$cost_price),$order->getExpectedProfit())); ?></span>
                                            </td>
                                        </tr>
                                       
                                    </table>
                                </div>
                                <div class="row" style="margin: 0;">
                                    <div class="col-sm-10">
                                        <?php
                                        $orderExpectedCost = new \backend\models\OrderExpectedCost();
                                        $form = ActiveForm::begin(['layout' => 'inline',
                                            'action' => ['order-action/expected-cost'],
                                            'validationUrl' => ['order-action/expected-cost','is_validate' => 1],
                                            'enableAjaxValidation' => true,
                                            'id' => 'expected-cost-form']);
                                        ?>
                                        <b>成本名称*</b>
                                        <div class="combobox form-control" style="border: none;">
                                            <input type="text" class="expected-cost-name">
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
                                        <?= Html::activeHiddenInput($orderExpectedCost, 'virtual_order_id',['value' => $order->virtual_order_id]) ?>
                                        <?= Html::activeHiddenInput($orderExpectedCost, 'order_id',['value' => $order->id]) ?>
                                        <?= Html::activeHiddenInput($orderExpectedCost, 'cost_name',['class' => 'class-cost-name']) ?>
                                        <?= $form->field($orderExpectedCost, 'cost_price')->textInput(['class' => 'form-control class-cost-price']) ?>
                                        <b>备注</b>
                                        <?= $form->field($orderExpectedCost, 'remark')->textInput() ?>
                                        <?php if (Yii::$app->user->can('expected-cost/insert')): ?>
                                            <button type="submit" class="btn btn-w-m btn-primary">增加</button>
                                        <?php endif; ?>
                                        <?php
                                        $this->registerJs(<<<JS
	                                    $(function() 
	                                    {
	                                        var form = $('#expected-cost-form');
	                                        //订单业绩提点月
	                                        form.on('beforeSubmit', function()
	                                        {
	                                            $.post(form.attr('action'), form.serialize(), function(rs)
	                                            {
	                                                if(rs.status !== 200)
	                                                {
	                                                    $('.expected-cost-warning').text(rs.message);
	                                                }else{
                                                        var data = 
                                                        "<tr style='background: none;'>" +
                                                         "<td class='text-center'>"+rs.data.created_at+"</td>" +
                                                         "<td class='text-center'>录入</td>" +
                                                         "<td class='text-center'>"+rs.data.cost_name+"</td>" +
                                                         "<td class='text-center'>¥"+rs.data.cost_price+"</td>" +
                                                         "<td class='text-center'>"+rs.data.remark+"</td>" +
                                                         "<td class='text-center'>"+rs.data.creator_name+"</td>" +
                                                         "</tr>";
                                                        $("#zhao").append(data);
                                                        var costPrice = parseFloat(rs.data.cost_price);
	                                                    var cost = parseFloat($('.cost-amount').attr('data-cost').replace(/[,¥]+/g,''));
	                                    				var profit = parseFloat($('.profit-amount').attr('data-profit').replace(/[,¥]+/g,''));
	                                    				var surplus = parseFloat($('.surplus-profit').attr('data-surplus').replace(/[,¥]+/g,''));
	                                    				var newCost = costPrice + cost;
	                                                    var newProfit = profit - costPrice;
	                                                    var newSurplus = surplus- costPrice;
	                                                    $('.cost-amount').empty().text('预计成本金额：¥'+ fomatFloat(newCost)).attr('data-cost','¥'+ fomatFloat(newCost));
	                                                    $('.profit-amount').empty().text('预计利润金额：¥'+ fomatFloat(newProfit)).attr('data-profit','¥'+ fomatFloat(newProfit));
	                                                    $('.surplus-profit').empty().text('剩余可计算预计利润：¥'+ fomatFloat(newSurplus)).attr('data-surplus','¥'+ fomatFloat(newSurplus));
	                                                    
	                                                    //制保留2位小数，如：2，会在2后面补上00.即2.00  
													    function fomatFloat(num) {  
													        var f = parseFloat(num);  
													        if (isNaN(f)) {  
													            return false;  
													        }  
													        var f = Math.round(num*100)/100;  
													        var s = f.toString();  
													        var rs = s.indexOf('.');  
													        if (rs < 0) {  
													            rs = s.length;  
													            s += '.';  
													        }  
													        while (s.length <= rs + 2) {  
													            s += '0';  
													        }  
													        return s;  
													    }
	                                                }
	                                            },'json');
	                                            return false;
	                                        });
	                                    })
JS
                                        );
                                        ?>
                                        <?php ActiveForm::end(); ?>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-w-m btn-success calculate-expected-btn" <?php if($virtualModel->payRate()): ?>disabled<?php endif; ?> data-target="#calculate-expected-cost-modal" data-toggle="modal">计算预计利润(子)</button>
                                    </div>
                                </div>
                                <!--计算预计利润（子）start-->
                                <?php if (Yii::$app->user->can('virtual-order-action/payment-mode')): ?>
                                    <div class="modal fade" id="calculate-expected-cost-modal" role="dialog" aria-labelledby="modal-title">
                                        <?php
                                        $calculateOrderExpectedCostForm = new \backend\models\CalculateOrderExpectedProfitForm();
                                        $form = \yii\bootstrap\ActiveForm::begin([
                                            'action' => ['virtual-order-action/calculate-order-expected-profit'],
                                            'id' => 'calculate-expected-form',
                                            'validationUrl' => ['virtual-order-action/calculate-order-expected-profit', 'is_validate' => 1],
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
                                                    <h4 class="modal-title">计算预计利润（子）</h4>
                                                </div>
                                                <div class="modal-body input_box clerk-div">
                                                    确定要计算预计利润吗？计算后，结果将不可逆转！
                                                    <p class="text-danger warning-active"><?= Html::error($calculateOrderExpectedCostForm, 'order_id'); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <?= Html::activeHiddenInput($calculateOrderExpectedCostForm, 'order_id',['value' => $model->id]); ?>
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                                    <button type="submit" class="btn btn-primary clerk-sure-btn">确定</button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        $this->registerJs(<<<JS
                                        $('.calculate-expected-btn').click(function()
                                        {
                                            var form = $('#calculate-expected-form');
                                            form.trigger('reset.yiiActiveForm');
                                            form.find('.warning-active').text('');
                                        });
                                        
                                        $('#calculate-expected-form').on('beforeSubmit', function()
                                        {
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
                                <!--计算预计利润（子）end-->

                                <p class="text-danger expected-cost-warning" style="padding-left: 15px;margin: 10px 0 0;"></p>

                                <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>实际成本管理</b>：<span class="text-info">(成本一旦增加后，不允许删除，若输入错误，可再增加一笔成本进行回冲，如多输入了一笔刻章费，300元，须再增加一笔刻章费，-300元，备注里务必说明情况)</span></p>
                                <?php if (Yii::$app->user->can('cost/list')): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id = "test">
                                    <thead>
                                    <tr>
                                    	<th class="text-center" style="width: 304px;">增加时间</th>
                                    	<th class="text-center" style="width: 304px;">成本类型</th>
                                        <th class="text-center" style="width: 265px;">成本名称</th>
                                        <th class="text-center" style="width: 265px;">成本金额</th>
                                        <th class="text-center" style="width: 265px;">备注</th>
                                        <th class="text-center" style="width: 224px;">操作人</th>
                                    </tr>
                                    </thead>
                                    <tbody id="cost-record-list">
                                    <?php
                                    $cost_price = 0;
                                    foreach ($order->orderCostRecord as $costRecord): ?>
                                        <tr style="background: none;">
                                            <td class="text-center"><?= Yii::$app->formatter->asDatetime($costRecord->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                            <td class="text-center"><?= $costRecord->getTypeName(); ?></td>
                                            <td class="text-center"><?= $costRecord->cost_name; ?></td>
                                            <td class="text-center"><?= $costRecord->cost_price; ?></td>
                                            <td class="text-center"><?= $costRecord->remark; ?></td>
                                            <td class="text-center"><?= $costRecord->creator_name; ?></td>
                                        </tr>
                                        <?php $cost_price += floatval($costRecord->cost_price); ?>
                                    <?php endforeach; ?>
                                    <tr style="background: none;">
                                        <td colspan="6" class="text-right">
                                            <span class="text-danger" id="cost-price">成本金额总计：<?= Decimal::formatYenCurrentNoWrap($cost_price); ?></span>&nbsp;&nbsp;&nbsp;
                                            <span id="real-price" class="text-danger">实际利润：<?= Decimal::formatYenCurrentNoWrap(BC::sub(BC::sub($order->price,$cost_price),$order->refund_amount)); ?></span>
                                        </td>
                                    </tr>
                                    </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                                <?php
                                $orderCostRecord = new \common\models\OrderCostRecord();
                                $form = ActiveForm::begin(['layout' => 'inline','action' => ['order-cost-record/create'],'id' => 'cost-form']); ?>
                                <b>成本名称*</b>
                                <div class="combobox form-control" style="border: none;">
                                    <input type="text" class = "cost-name">
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
                                <?= Html::activeHiddenInput($orderCostRecord, 'virtual_order_id',['value' => $order->virtual_order_id]) ?>
                                <?= Html::activeHiddenInput($orderCostRecord, 'order_id',['value' => $order->id]) ?>
                                <?= Html::activeHiddenInput($orderCostRecord, 'cost_name',['class' => 'class-cost-name']) ?>
                                <?= $form->field($orderCostRecord, 'cost_price')->textInput(['class' => 'form-control class-cost-price']) ?>
                                <b>备注</b>
                                <?= $form->field($orderCostRecord, 'remark')->textInput() ?>

                                <?php if (Yii::$app->user->can('order-cost-record/*')): ?>
                                    <button type="button" class="btn btn-w-m btn-primary cost-btn">增加</button>
                                <?php endif; ?>

                                <span class="error-text" style="color: red;"></span>
                                <?php ActiveForm::end(); ?>
                                <!--成本类型库弹框开始-->
                                <div class="modal fade cost-modal" id="edit_cost" tabindex="-1" role="dialog" aria-labelledby="modal-title">
                                    <?php Pjax::begin(); ?>
                                    <?= $this->render('cost',['provider' => $provider]) ?>
                                    <?php Pjax::end(); ?>
                                </div>
                                <!--成本类型库弹框结束-->
                                <br>
                                <?php if (Yii::$app->user->can('cost/list')): ?>
                                    <br>
                                    <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>已计算预计利润历史记录</b>：
                                        <?php if (Yii::$app->user->can('order/profit-update')): ?>
                                        <button class="btn btn-w-m btn-success correct-btn" data-target="#correct-price-modal" data-toggle="modal" <?php if($virtualModel->payRate()):?>disabled<?php endif;?> type="button">预计利润更正</button>
                                        <?php endif; ?>

                                    </p>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                            <tr>
                                                <th class="text-center" style="width: 304px;">计算时间</th>
                                                <th class="text-center" style="width: 265px;">计算类型</th>
                                                <th class="text-center" style="width: 265px;">金额归属对象/部门/公司</th>
                                                <th class="text-center" style="width: 265px;">金额名称</th>
                                                <th class="text-center" style="width: 265px;">金额</th>
                                                <th class="text-center" style="width: 224px;">操作人</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php if($order->expectedProfits): ?>
                                                <?php foreach ($order->expectedProfits as $expectedProfit): ?>
                                                    <tr>
                                                        <td  class="text-center"><?= date('Y-m-d H:i:s',$expectedProfit->created_at); ?></td>
                                                        <td  class="text-center"><?= $expectedProfit->getTypeName() ?></td>
                                                        <td  class="text-center"><?= $expectedProfit->administrator_name.'/'.$expectedProfit->department_name.'/'.$expectedProfit->company_name ?></td>
                                                        <td  class="text-center"><?= $expectedProfit->title ?></td>
                                                        <td  class="text-center"><?= $expectedProfit->expected_profit ?></td>
                                                        <td  class="text-center"><?= $expectedProfit->creator_name ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--预计利润更正start-->
                                    <div class="modal fade" id="correct-price-modal" role="dialog" aria-labelledby="adjust-order-price-label">
                                        <?php
                                        $correctForm = new \backend\models\ExpectedProfitCorrectForm();
                                        $form = \yii\bootstrap\ActiveForm::begin([
                                            'action' => ['virtual-order-action/expected-profit-correct'],
                                            'id' => 'expected-profit-correct-form',
                                            'validationUrl' => ['virtual-order-action/expected-profit-correct', 'is_validate' => 1],
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
                                                    <h4 class="modal-title" id="adjust-order-price-label">订单预计利润金额更正</h4>
                                                </div>
                                                <div class="modal-body input_box">
                                                    <?= $form->field($correctForm, 'correct_price')->textInput(); ?>
                                                    <?= $form->field($correctForm, 'title')->textInput() ?>
                                                    <?= $form->field($correctForm, 'content')->textarea() ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <?= Html::activeHiddenInput($correctForm, 'order_id', ['value' => $order->id]); ?>
                                                    <span class="text-danger warning-active"></span>
                                                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                                                    <button type="submit" class="btn btn-primary sure-btn">确定</button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        $this->registerJs(<<<JS
                                            $(function() 
                                            {
                                                var form = $('#expected-profit-correct-form');
                                                $('.correct-btn').click(function() 
                                                {
                                                    form.trigger('reset.yiiActiveForm');
                                                    form.find('.warning-active').text('');
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
                                        ) ?>
                                        <?php \yii\bootstrap\ActiveForm::end(); ?>
                                    </div>
                                    <!--预计利润更正end-->
                                <?php endif; ?>
                                <?php
                                $cost_url = Url::to(['order-cost-record/create']);
                                $this->registerJs(<<<JS
$('.effective-sure-btn').click(function()
{
     $.post($('#performance-statistics-form').attr('action'), $('#performance-statistics-form').serialize(), function(rs)
     {
        if(rs.status != 200)
	    {
	        $('.performance-warning-active').html(rs.message);
	    }else{
            window.location.reload();
	    }
	 })
});
$('.expected-cost-name').change(function() 
{
    $('#orderexpectedcost-cost_name').val($(this).val());
});  

$('.cost-name').change(function() 
{
    var order_cost_name = $(this).val();
    var record_cost_name = $('#ordercostrecord-cost_name').val();
    if(order_cost_name && record_cost_name == '')
    {
       $('#ordercostrecord-cost_name').val(order_cost_name);
    }
});                    
//订单成本录入       
$('.cost-btn').click(function()
{
     $.post('{$cost_url}', $('#cost-form').serialize(), function(rs)
     {
        if(rs.status != 200)
	    {
	        $('.error-text').html(rs.message);
	    }else
	    {
	        window.location.reload();
	    }
     })
});

//下拉框js
$(function()
{
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
            $(this).parents('form').find('.class-cost-name').val(val);
            $(this).parents('form').find('.class-cost-price').val(price);
        })
        }
     
		$(name).children('div').find('p').mousedown(function(){
		$(this).parents(name).find('div').hide();
		$('.cost-modal').modal('show');
		$('#edit_cost form').attr('action', '{$costItemUrl}');
		})
	}
})
JS
                                )?>
                            </div>
                        </div>
                        <!--成本信息end-->

                        <?php if (Yii::$app->user->can('performance/list')): ?>
                        <!--业绩管理start-->
                        <div id="tab-order-performance" class="tab-pane <?php if($sign): ?>active<?php endif; ?>">
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th class="text-center" style="width: 108px;">年-月</th>
                                        <th class="text-center" style="width: 136px;">已付款</th>
                                        <th class="text-center" style="width: 136px;">退款</th>
                                        <th class="text-center" style="width: 136px;">未付款</th>
                                        <th class="text-center" style="width: 136px;">录入成本金额</th>
                                        <th class="text-center" style="width: 136px;">实际利润金额</th>
                                        <th class="text-center" style="width: 136px;">已计算实际利润金额</th>
                                        <th class="text-center" style="width: 136px;">更正实际利润金额</th>
                                        <th class="text-center" style="width: 136px;">提成金额</th>
                                        <th class="text-center" style="width: 136px;">剩余可计算实际利润金额</th>
                                        <th class="text-center" style="width: 136px;">操作</th>
                                    </tr>
                                    </thead>
                                    <tbody id="performance-list">
                                    <?php
                                    $total_already_paid = 0;//已付款总计
                                    $cost_total_price = 0;  //成本金额总计
                                    $total_performance = 0; //业绩总计
                                    $already_performance = 0;  //已计算业绩总计
                                    $lave_performance = 0;  //剩余计算业绩总计
                                    if($order->performanceRecord):?>
                                    <?php foreach($order->performanceRecord as $item):
                                        $total_already_paid += floatval($item->already_paid);
                                        $cost_total_price += floatval($item->cost);
                                        $total_performance += floatval($item->performance);
                                        $already_performance += floatval($item->getCalculatedPerformance());
                                        $lave_performance += $item->lavePerformance();//剩余计算业绩
                                        ?>
                                    <tr class="performance-record-item" data-id="<?= $item->id; ?>" style="background: none;">
                                        <td class="text-center"><?= $item->year.'年-'.$item->month.'月' ?></td>
                                        <td class="text-center"><?= $item->already_paid; ?></td>
                                        <td class="text-center"><?= $item->refunds ?></td>
                                        <td class="text-center"><?= $item->pending_pay ?></td>
                                        <td class="text-center"><?= $item->cost; ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($item->performance); ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($item->getCalculatedPerformance()); ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($item->getCorrectPrice()); ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($item->getReward()); ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($item->lavePerformance()); ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary calculate-profit-btn" data-target="#count-performance"
                                                <?php if(!(Yii::$app->user->can('performance-statistics/*') && $order->expectedProfits && $order->salesman_aid && $item->lavePerformance() != 0)): ?>
                                                    disabled<?php endif; ?>
                                                    data-toggle="modal">计算提成</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                        <tr style="background: none;">
                                            <td colspan="11" class="text-right">
                                                成本金额总计：<span class="text-danger"><?= Decimal::formatYenCurrentNoWrap($cost_total_price); ?></span>&nbsp;
                                                实际利润金额总计：<span class="text-danger"><?= Decimal::formatYenCurrentNoWrap($total_performance); ?></span>&nbsp;
                                                已计算实际利润金额总计：<span class="text-danger"><?= Decimal::formatYenCurrentNoWrap($already_performance); ?></span>&nbsp;
                                                剩余可计算实际利润金额总计：<span class="text-danger"><?= Decimal::formatYenCurrentNoWrap($lave_performance); ?></span>&nbsp;
                                                提成金额总计：<span class="text-danger"><?= Decimal::formatYenCurrentNoWrap($order->getSalary()); ?></span>&nbsp;
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                                </div>
                                <p>
                                    <?php if(Yii::$app->user->can('virtual-order-action/performance-correct')): ?>
                                    <button class="btn btn-sm btn-primary correct-performance-btn" data-target="#correct-performance" data-toggle="modal">提成金额更正</button>
                                    <?php endif; ?>
                                    <!--业绩更正金额开始-->
                                    <div class="modal fade" id="correct-performance" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                        <?php
                                        $performanceCorrectForm = new \backend\models\PerformanceCorrectForm();
                                        $form = \yii\bootstrap\ActiveForm::begin([
                                            'action' => ['virtual-order-action/performance-correct'],
                                            'validationUrl' => ['virtual-order-action/performance-correct', 'is_validate' => '1'],
                                            'enableAjaxValidation' => false,
                                            'id' => 'performance-correct-form',
                                            'layout' => 'horizontal',
                                            'fieldConfig' => [
                                                'horizontalCssClasses' => [
                                                    'label' => 'col-sm-3',
                                                    'wrapper' => 'col-sm-7',
                                                    'hint' => 'col-sm-2'
                                                ],
                                            ],
                                        ]); ?>
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title">订单提成金额更正</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?= $form->field($performanceCorrectForm, 'correct_price')->textInput(); ?>
                                                    <?= $form->field($performanceCorrectForm, 'rate')->textInput()->hint('%'); ?>
                                                    <?= $form->field($performanceCorrectForm, 'title')->textInput(); ?>
                                                    <?= $form->field($performanceCorrectForm, 'content')->textarea(); ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <?= Html::activeHiddenInput($performanceCorrectForm,'order_id',['value' => $order->id]); ?>
                                                    <span class="text-danger warning-active"></span>
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                                    <button type="submit" class="btn btn-primary correct-sure-btn">立即计算</button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        $allCreateUrl = Url::to(['performance-statistics/all-create']);
                                        $this->registerJs(<<<JS
                                        //更正业绩
                                        $(function() 
                                        {
                                            var form = $('#performance-correct-form');
                                            $('.correct-performance-btn').click(function() 
                                            {
                                                form.trigger('reset.yiiActiveForm');
                                                form.find('.warning-active').empty();
                                            });
                                            
                                            form.on('beforeSubmit', function()
                                            {
                                                form.find('.correct-sure-btn').text('计算中...').attr('disabled','disabled');
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
                                                        form.find('.correct-sure-btn').empty().text('确定').removeAttr('disabled');
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
                                    <!--业绩更正金额结束-->
                                </p>
                                <!--业绩-已计算历史开始-->
                                <p style="border-top: 3px solid #e7eced;width: 103%;margin:0;margin-left: -1.5%;"></p>
                                <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>已计算提成历史记录</b>：</p>
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>计算时间</th>
                                        <th>计算类型</th>
                                        <th>提点类型</th>
                                        <th>业务员/部门/公司</th>
                                        <th>提点</th>
                                        <th>金额名称</th>
                                        <th>已计算实际利润金额</th>
                                        <th>提成金额</th>
                                        <th>操作人</th>
                                    </tr>
                                    </thead>
                                    <tbody id="performance-list">
                                    <?php if($order->performanceStatistics): ?>
                                        <?php foreach($order->performanceStatistics as $item): ?>
                                            <tr class="performance-record-item" data-id="<?= $item->id; ?>" style="background: none;">
                                                <td><?= date('Y-m-d H:i:s',$item->created_at); ?></td>
                                                <td><?= $item->getTypeName(); ?></td>
                                                <td><?= $item->getAlgorithmName(); ?></td>
                                                <td><?= $item->administrator_name.'/'.$item->department_name.'/'.$item->administrator->company->name; ?></td>
                                                <td><?= floatval($item->reward_proportion).'%'; ?></td>
                                                <td><?= $item->title; ?></td>
                                                <td><?= Decimal::formatYenCurrentNoWrap($item->calculated_performance); ?></td>
                                                <td><?= Decimal::formatYenCurrentNoWrap($item->performance_reward); ?></td>
                                                <td><?= $item->creator_name; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                                <!--业绩-已计算历史结束-->
                            </div>
                            <?php if(Yii::$app->user->can('performance-statistics/*')): ?>
                            <!--计算业绩开始-->
                            <div class="modal fade" id="count-performance" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                <?php
                                $performanceStatisticsForm = new PerformanceStatisticsForm();
                                $form = \yii\bootstrap\ActiveForm::begin([
                                    'action' => ['performance-statistics/create'],
                                    'validationUrl' => ['performance-statistics/create', 'is_validate' => '1'],
                                    'enableAjaxValidation' => false,
                                    'id' => 'performance-statistics-form',
                                    'layout' => 'horizontal',
                                    'fieldConfig' => [
                                        'horizontalCssClasses' => [
                                            'label' => 'col-sm-3',
                                            'wrapper' => 'col-sm-7',
                                            'hint' => 'col-sm-2'
                                        ],
                                    ],
                                ]); ?>
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">计算提成</h4>
                                        </div>
                                        <div class="modal-body">
                                            <?= $form->field($performanceStatisticsForm, 'rate')->textInput()->hint('%'); ?>
                                            <div class="form-group">
                                                <label class="control-label col-sm-3"></label>
                                                <div class="col-sm-7 text-danger">计算当前月的实际利润金额的百分之多少。</div>
                                            </div>
                                            <?= $form->field($performanceStatisticsForm, 'point')->checkbox(); ?>
                                            <?= $form->field($performanceStatisticsForm, 'fix_point_id')->dropDownList(\common\models\FixedPoint::getFixPoint()); ?>
                                            <?= Html::activeHiddenInput($performanceStatisticsForm, 'performance_record_id'); ?>
                                        </div>
                                        <div class="modal-footer">
                                            <span class="text-danger warning-active"></span>
                                            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                            <button type="submit" class="btn btn-primary statistics-sure-btn">立即计算</button>
                                        </div>
                                        <?php
                                        $allCreateUrl = Url::to(['performance-statistics/all-create']);
                                        $this->registerJs(<<<JS
                                //计算业绩
                                $(function() 
                                {
                                    $('.field-performancestatisticsform-fix_point_id').hide();
                                    var form = $('#performance-statistics-form');
                                    $('.calculate-profit-btn').click(function() 
                                    {
                                        form.trigger('reset.yiiActiveForm');
                                        form.find('.warning-active').empty();
                                        var id = $(this).parents('.performance-record-item').attr('data-id');
                                        $('#performancestatisticsform-performance_record_id').val(id);
                                    });
                                    $('#performancestatisticsform-point').click(function() 
                                    {
                                        if($(this).is(':checked'))
                                        {
                                            $('.field-performancestatisticsform-fix_point_id').show();
                                        }
                                        else
                                        {
                                            $('.field-performancestatisticsform-fix_point_id').hide();
                                        }
                                    });
                                
                                    form.on('beforeSubmit', function()
                                    {
                                        form.find('.statistics-sure-btn').text('计算中...').attr('disabled','disabled');
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
                                                form.find('.statistics-sure-btn').empty().text('立即计算').removeAttr('disabled');
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
                                <?php endif; ?>
                            </div>
                            <!--业绩管理end-->
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!--订单详情页(订单记录、短信记录、文件上传)end-->
        <?php
        $this->registerJs(<<<JS
            $(function(){
                var orderCount = $('.table-order tbody tr').length;
                var orderMessage = $('.table-message tbody tr').length;
                var orderFile = $('.table-file tbody tr').length;
                if (orderCount > 5){
                    var tr1 = $('.table-order tbody tr:gt(4)');
                    tr1.hide();
                    $('#tab-order-record .more-end-btn p').on('click',function(){
                        var trhtml = $(this).html();
                        if(trhtml == '查看更多'){
                            tr1.show();
                            $(this).html('收起');
                        }else{
                            tr1.hide();
                            $(this).html('查看更多');
                        }
                    });
                }else {
                    $('.table-order').next('.more-end-btn').hide();
                }
                if (orderMessage > 5){
                    var tr2 = $('.table-message tbody tr:gt(4)');
                    tr2.hide();
                    $('#tab-order-sms .more-end-btn p').on('click',function(){
                        tr2.show();
                        var trhtml = $(this).html();
                        if(trhtml == '收起'){
                            tr2.hide();
                            $(this).html('查看更多');
                        }else{
                            $(this).html('收起');
                        }
                    });
                }else {
                    $('.table-message').next('.more-end-btn').hide();
                }
                if (orderFile > 5){
                    console.log(orderFile);
                    var tr3 = $('.table-file tbody tr:gt(4)');
                    tr3.hide();
                    $('#tab-order-file .more-end-btn p').on('click',function(){
                        tr3.show();
                        var trhtml = $(this).html();
                        if(trhtml == '收起'){
                            tr3.hide();
                            $(this).html('查看更多');
                        }else{
                            $(this).html('收起');
                        }
                    });
                }else {
                    $('.table-file').next('.more-end-btn').hide();
                }
                
            });

            
JS
        ) ?>
        <!--订单信息start-->
        <?php if ($model != null): ?>
            <div class="col-lg-12">
                <?php if ($model->virtualOrder->orders != null && count($model->virtualOrder->orders) > 1): ?>
                    <?php if ($model->virtualOrder->package_id > 0):?>
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>
                                            <h5>客户购买的【<?= $model->virtualOrder->package_name?>】中还包含以下商品：：</h5>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>订单号</th>
                                        <th>所购商品</th>
                                        <th>公司名称</th>
                                        <th>服务人员/手机号</th>
                                        <th>订单状态</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($model->virtualOrder->orders as $order): ?>
                                        <?php if ($order->sn != $model->sn): ?>
                                            <tr>
                                                <td><?= $order->sn ?></td>
                                                <td><?= $order->product_name ?></td>
                                                <td>
                                                    <?php if($order->businessSubject): ?>
                                                        <?= $order->businessSubject->company_name ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($order->clerk): ?>
                                                        <?= $order->clerk->name; ?>/<?= $order->clerk->phone; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $order->getStatusName() ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else:?>
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>
                                            <h5><i class="border-left-color m-r-sm"></i>客户同时还购买了以下其他商品：</h5>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>订单号</th>
                                        <th>所购商品</th>
                                        <th>公司名称</th>
                                        <th>服务人员/手机号</th>
                                        <th>订单状态</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($model->virtualOrder->orders as $order): ?>
                                        <?php if ($order->sn != $model->sn): ?>
                                            <tr>
                                                <td><?= $order->sn ?></td>
                                                <td><?= $order->product_name ?></td>
                                                <td><?= $order->company_name ?></td>
                                                <td>
                                                    <?php if (!empty($order->clerk)): ?>
                                                        <?= $order->clerk->name; ?>/<?= $order->clerk->phone; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $order->getStatusName() ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif;?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <!--订单信息end-->

        <!--客户信息start-->
        <?php if ($model->user != null && 1!=1): ?>
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>客户信息 </h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>姓名/昵称</th>
                                    <th>客户头像</th>
                                    <th>手机号码</th>
                                    <th>常用邮箱</th>
                                    <th>邮寄地址</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?= $model->user->name; ?></td>
                                    <td>
                                        <img class="thumbnail margin0"
                                             src="<?= $model->user->getImageUrl(50,50) ?>"/>
                                    </td>
                                    <td><?= $model->user->phone; ?></td>
                                    <td><?= $model->user->email; ?></td>
                                    <td><?= $model->user->address; ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!--客户信息end-->

        <!--评价信息start-->
        <?php if (isset($model) && isset($model->orderEvaluate->is_audit)): ?>
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>评价信息 </h5>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 col-md-4">
                            <div class="ibox-content">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>综合评价</th>
                                            <th>专业程度</th>
                                            <th>办事效率</th>
                                            <th>服务态度</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($model->orderEvaluate != null): ?>
                                            <tr>
                                                <td><?= $model->orderEvaluate->complex_score; ?>分</td>
                                                <td><?= $model->orderEvaluate->pro_score; ?>分</td>
                                                <td><?= $model->orderEvaluate->efficiency_score; ?>分</td>
                                                <td><?= $model->orderEvaluate->attitude_score; ?>分</td>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-8">
                            <div class="ibox-content">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>标签</th>
                                            <th>评价</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>
                                                <?php $tags = $model->orderEvaluate->getTagList() ?>
                                                <?php foreach ($tags as $tag): ?>
                                                    <span class="label label-default"><?= $tag; ?></span>
                                                <?php endforeach; ?>
                                            </td>
                                            <td>
                                                <div>客户评价：<?= $model->orderEvaluate->evaluate_content; ?></div>
                                                <div>客服回复：<?= $model->orderEvaluate->reply_content; ?></div>
                                                <?php if (Yii::$app->user->can('order-evaluate/reply')): ?>
                                                    <?php if (!$model->orderEvaluate->is_reply): ?>
                                                        <button id="add-flow-node" class="btn btn-xs btn-primary reply-btn"
                                                                data-target="#order-evaluate-modal"
                                                                data-toggle="modal"
                                                                data-id="<?= $model->orderEvaluate->id ?>"
                                                                data-order-id="<?= $model->orderEvaluate->order_id ?>">回复评价
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif;?>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!--评价信息end-->
    </div>
    <!--回复评价弹框start-->
    <div class="modal fade" id="order-evaluate-modal" role="dialog" aria-labelledby="modal-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php
                $orderEvaluateForm = new \backend\models\OrderEvaluateForm();
                $form = \yii\bootstrap\ActiveForm::begin([
                    'action' => ['order-evaluate/reply', 'id' => '__id__', 'order_id' => '__order_id__'],
                    'id' => 'order-evaluate-form',
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
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">回复评价</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($orderEvaluateForm, 'evaluate_content')->textarea(['disabled' => 'disabled']) ?>
                    <?= $form->field($orderEvaluateForm, 'reply_content')->textarea(['maxlength' => 80]) ?>
                    <?= Html::activeHiddenInput($orderEvaluateForm, 'order_id'); ?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary refund-sure-btn">确定</button>
                </div>
                <?php
                $orderEvaluateInfoUrl = \yii\helpers\Url::to(['order-evaluate/ajax-virtual-order-info', 'id' => '__id__']);
                $this->registerJs(<<<JS
                $('.reply-btn').click(function(){
                    var id = $(this).attr('data-id');
                    var order_id = $(this).attr('data-order-id');
                    var form = $('#order-evaluate-form');
                    form.attr('action', form.attr('action').replace('__id__', id).replace('__order_id__', order_id));
                    $('#order-evaluate-form').find('#orderevaluateform-order_id').val(order_id);
                    //获取评价详情
                    $.get('{$orderEvaluateInfoUrl}'.replace('__id__', id),function(rs){
                        if(rs.status==200){
                            $('#orderevaluateform-evaluate_content').val(rs.model.evaluate_content);
                        }
                    },'json');
                });
JS
                ); ?>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
            </div>
        </div>
    </div>
    <!--回复评价弹框end-->

    <!--单独文件上传弹框start-->
<?php if (Yii::$app->user->can('order-action/do-flow-action') || Yii::$app->user->can('order-action/upload')): ?>
    <div class="modal fade" id="order-file-upload-modal" role="dialog"
         aria-labelledby="modal-title">
        <?php
        $orderFileModel = new OrderFileSaveForm();
        $orderFileModel->order_id = $model->id;
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['order-action/save-file'],
            'validationUrl' => ['order-action/save-file', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'id' => 'order-file-upload-form',
            'layout' => 'default',
        ]); ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">上传文件</h4>
                </div>
                <div class="modal-body input_box">
                    <!--                    <div class="upload-hint">-->
                    <!--                        <span>正在上传</span>-->
                    <!--                        <i class="upload-img"></i>-->
                    <!--                    </div>-->
                    <?= $form->field($orderFileModel, 'remark')->textarea() ?>
                    <?php
                    $orderFileUploadForm = new OrderFileUploadForm();
                    echo $form->field($orderFileUploadForm, 'file')->widget(JQFileUpLoadWidget::className(), [
                        'labelClass' => 'btn btn-sm btn-primary btn-upload',
                        'buttonTitle' => '<span class="file-upload-title">上传</span>',
                        'name' => 'file',
                        'serverUrl' => ['order-action/upload', 'is_flow' => '0'],
                        'formData' =>[
                            Yii::$app->request->csrfParam => Yii::$app->request->csrfToken,
                        ],
                        'submit' => new \yii\web\JsExpression('function (e, data) {
                                    data.formData = {
                                        file_id: $("#orderfilesaveform-file_id").val(), 
                                        order_id: ' . $model->id . ',
                                        "'.Yii::$app->request->csrfParam.'": "'.Yii::$app->request->csrfToken.'"
                                    };
                                    $(".modal-title").show();
                                    $(".file-upload-title").text("正在上传");
                                    $(".sure-btn").attr("disabled","true");
                                    return true;
                                }'),
                        'done' => new \yii\web\JsExpression('function (e, data) {
                                $.each(data.result.files, function (index, file) {
                                    if(file.error)
                                    {
                                        $("#order-file-upload-modal .warning-active").text(file.error);
                                    }
                                    else
                                    {
                                        $("#order-file-upload-modal .files-list").append($(document.createElement("li")).append(file.name+"&nbsp;<a href=\"javascript:void(0)\" style=\"color: red\" class=\"delete\" data-key="+file.key+" data-id="+file.id+">移除</a>"));
                                        $("#orderfilesaveform-file_id").val(file.id);
                                        $(".file-upload-title").text("上传");
                                        $(".sure-btn").removeAttr("disabled");
                                    }
                                });
                            }')
                    ])->hint('文件大小需在10M以内，文件越大上传越慢，建议大文件压缩上传。'); ?>
                    <?= $form->field($orderFileModel, 'is_see')->checkbox() ?>
                    <ul class="files-list"></ul>
                    <?= Html::activeHiddenInput($orderFileModel, 'file_id', ['class' => 'upload-file-id']) ?>
                    <?php $order_file_sms_id = Property::get('order_file_sms_id'); ?>
                    <?php $order_file_sms_preview = Property::get('order_file_sms_preview'); ?>
                    <?php if(!empty($order_file_sms_id) && !empty($order_file_sms_preview) && $model->clerk): ?>
                        <h4>将给客户发送以下信息：</h4>
                        <p>
                            <?= str_replace(['{1}','{2}','{3}'], [$model->clerk->address, $model->clerk->name, $model->clerk->phone], $order_file_sms_preview)?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($orderFileModel, 'order_id') ?>
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary sure-btn sure-btn-disabled" disabled="disabled" >确定</button>
                </div>
            </div>
        </div>
        <?php
        $delete = \yii\helpers\Url::to(['order/delete']);
        $this->registerJs(<<<JS
            $('.files-list').on('click', '.delete', function(){
               var id = $(this).attr('data-id');
               var key = $(this).attr('data-key'); 
               var _this = $(this);
               $.post('{$delete}',{file_id:id,key:key}, function(rs){
               if(rs.status === 200)
               {
                   $(".delete").off("click");
                   if(!rs['has_file'])
                   {
                       $(_this).parent().parent().parent().parent().find('.modal-footer .sure-btn-disabled').attr("disabled","true");
                       $(_this).parent().parent().parent().find('.upload-file-id').val('');
                   }
                   _this.parent().remove();
               }
               else
               {
                   alert(rs.message);
               }
               
              }, 'json');                   
           });
        
        $('#order-file-upload-form').on('beforeSubmit', function(){
            var form = $(this);
            $.post(form.attr('action'), form.serialize(), function(rs){
                if(rs.status === 200)
                {
                    form.trigger('reset.yiiActiveForm');
                    window.location.replace('{$url}');
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
<?php endif;?>
    <!--单独文件上传弹框end-->

    <!--添加备注start-->
<?php if (Yii::$app->user->can('order-action/add-remark')): ?>
    <div class="modal fade" id="order-remark-modal" role="dialog"
         aria-labelledby="modal-title">
        <?php
        $orderRemarkModel = new OrderRemarkForm();
        $orderRemarkModel->order_id = $model->id;
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['order-action/add-remark'],
            'validationUrl' => ['order-action/add-remark', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'id' => 'order-remark-form',
            'layout' => 'default',
        ]); ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">添加备注</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($orderRemarkModel, 'remark')->textarea() ?>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($orderRemarkModel, 'order_id') ?>
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">确定</button>
                </div>
            </div>
        </div>
        <?php
        $this->registerJs(<<<JS
        $('#add-remark').click(function() {
            var form = $('#order-remark-form');
            form.find('.warning-active').text('');
            form.trigger('reset.yiiActiveForm');
        });
        $('#order-remark-form').on('beforeSubmit', function(){
            var form = $(this);
            $.post(form.attr('action'), form.serialize(), function(rs){
                if(rs.status === 200)
                {
                    form.trigger('reset.yiiActiveForm');
                    window.location.replace('{$url}');
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
<?php endif;?>
    <!--添加备注end-->

<?php /*流程操作按钮弹框 start*/ ?>
<?php if (Yii::$app->user->can('order-action/do-flow-action')): ?>
    <?php if ($model->flow && !$model->flowIsFinish() && $currentNode): ?>
        <?php foreach ($currentNode->actions as $action): ?>
            <div class="modal fade" id="flow-action-modal-<?= $action->id ?>" role="dialog"
                 aria-labelledby="modal-title">
                <?php
                $formModel = new OrderFlowActionForm();
                if ($action->isTypeUpload()) {
                    if ($action->hasInputList()) {
                        $formModel->setScenario('upload_text');
                    } else
                        $formModel->setScenario('upload');
                } else if ($action->isTypeDate()) {
                    if ($action->hasInputList()) {
                        $formModel->setScenario('input_date_text');
                    } else
                        $formModel->setScenario('input_date');
                } else if ($action->hasInputList()) {
                    $formModel->setScenario('input_text');
                }
                $formModel->order_id = $model->id;
                $formModel->flow_id = $model->flow_id;
                $formModel->node_id = $action->flow_node_id;
                $formModel->action_id = $action->id;
                $form = \yii\bootstrap\ActiveForm::begin([
                    'action' => ['order-action/do-flow-action'],
                    'validationUrl' => ['order-action/do-flow-action', 'is_validate' => 1],
                    'enableAjaxValidation' => true,
                    'id' => 'order-flow-action-form-' . $action->id,
                    'layout' => 'default',
                ]); ?>
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><?= $action->action_label; ?></h4>
                        </div>
                        <div class="modal-body input_box">
                            <?php
                            $smsPreview = str_replace(["\n", "\r", "\n\r"], ' ', $action->sms_preview);
                            $action_type = $action->type;
                            $hasSendVar = $action->isHasSendVar() ? '1' : '0';
                            $action_type_date = FlowNodeAction::TYPE_DATE;
                            $action_type_upload = FlowNodeAction::TYPE_UPLOAD;
                            $action_type_button = FlowNodeAction::TYPE_BUTTON;
                            ?>
                            <?php if ($action->isTypeDate()): ?>
                                <?= $form->field($formModel, 'input_date')->widget(DateTimePicker::className(), [
                                    'clientOptions' => [
                                        'format' => 'yyyy-mm-dd',
                                        'language' => 'zh-CN',
                                        'autoclose' => true,
                                        'minView' => 'month',
                                    ],
                                    'options' => [
                                        'id' => 'input_date-' . $action->id,
                                        'class' => 'form-control',
                                        'autocomplete' => 'off',
                                    ],
                                    'clientEvents' => [],
                                ])->label($action->action_hint) ?>
                            <?php elseif ($action->isTypeUpload()): ?>
                                <?= $form->field($formModel, 'remark')->textarea(['id' => 'input_text-' . $action->id]) ?>
                                <?php
                                $orderFileUploadForm = new OrderFileUploadForm();
                                echo $form->field($orderFileUploadForm, 'file')->widget(JQFileUpLoadWidget::className(), [
                                    'labelClass' => 'btn btn-sm btn-primary btn-upload',
                                    'buttonTitle' => '<span class="node-upload-title">上传</span>',
                                    'name' => 'file',
                                    'options' => ['id' => 'flow-upload-file'],
                                    'serverUrl' => ['order-action/upload'],
                                    'formData' =>[
                                        Yii::$app->request->csrfParam => Yii::$app->request->csrfToken,
                                    ],
                                    'submit' => new \yii\web\JsExpression('function (e, data) {
                                    data.formData = {
                                        file_id: $("#file_id-' . $action->id . '").val(), 
                                        order_id: ' . $model->id . ',
                                        flow_id: ' . $action->flow_id . ',
                                        node_id: ' . $action->flow_node_id . ',
                                        action_id: ' . $action->id . ',
                                        "'.Yii::$app->request->csrfParam.'": "'.Yii::$app->request->csrfToken.'"
                                    };
                                    $(".node-upload-title").text("正在上传");
//                                    $(".sure-btn").attr("disabled","true");
                                    $(".sure-btn-' . $action->id . '").attr("disabled","true");
                                    return true;
                                }'),
                                    'done' => new \yii\web\JsExpression('function (e, data) {
                                    $.each(data.result.files, function (index, file) {
                                        if(file.error)
                                        {
                                            $("#flow-action-modal-' . $action->id . ' .warning-active").text(file.error);
                                        }
                                        else
                                        {
                                            $("#flow-action-modal-' . $action->id . ' .files-list").append($(document.createElement("li")).append(file.name+"&nbsp;<a href=\"javascript:void(0)\" style=\"color: red\" class=\"delete\" data-key="+file.key+" data-id="+file.id+">移除</a>"));
                                            $("#file_id-' . $action->id . '").val(file.id);
                                            $(".node-upload-title").text("上传");
//                                            $(".sure-btn").removeAttr("disabled");
                                            $(".sure-btn-' . $action->id . '").removeAttr("disabled");
                                        }
                                    });
                                }')
                                ])->label($action->action_hint)->hint('文件大小需在10M以内，文件越大上传越慢，建议大文件压缩上传。'); ?>
                                <ul class="files-list"></ul>
                                <?= Html::activeHiddenInput($formModel, 'file_id', ['id' => 'file_id-' . $action->id, 'class' => 'upload-file-id']) ?>
                            <?php endif; ?>
                            <?php
                            if($action->isHasSendVar())
                            {
                                $smsPreview = str_replace(['{1}','{2}','{3}'], $model->clerk ? [$model->clerk->address, $model->clerk->name, $model->clerk->phone] : ['', '', ''], $smsPreview);
                            }
                            ?>
                            <?php $input_list = $action->getInputList(); ?>
                            <?php
                            $inputIds = [];
                            foreach ($input_list['input_list'] as $item):
                                $inputId = 'input_text-' . md5($item['label']) . '-' . $action->id;
                                $inputIds[] = $inputId;
                                ?>
                                <?= $form->field($formModel, 'input_text[' . md5($item['label']) . ']')->textInput(['id' => $inputId])->label($item['label']) ?>
                            <?php endforeach; ?>
                            <?= $form->field($formModel, 'is_send_sms')->checkbox(['id' => 'is_send_sms-'.$action->id])?>
                            <div class="is-can-disable-sms-<?= $action->id ?>">
                                <h4>将给客户发送以下信息：</h4>
                                <p id="sms-preview-<?= $action->id; ?>"></p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <?= Html::activeHiddenInput($formModel, 'order_id') ?>
                            <?= Html::activeHiddenInput($formModel, 'flow_id') ?>
                            <?= Html::activeHiddenInput($formModel, 'node_id') ?>
                            <?= Html::activeHiddenInput($formModel, 'action_id') ?>
                            <span class="text-danger warning-active"></span>
                            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                            <button type="submit" class="btn btn-primary sure-btn-<?= $action->id?> sure-btn-disabled" disabled="disabled">确定</button>
                        </div>
                    </div>
                </div>
                <?php
                $inputIdsJson = \yii\helpers\Json::encode($inputIds);
                $this->registerJs(<<<JS
             $(function(){
                
                function sendSms()
                {
                    if($('#is_send_sms-{$action->id}').is(':checked')){
                        $(".is-can-disable-sms-{$action->id}").show(); 
                    }else{
                        $(".is-can-disable-sms-{$action->id}").hide(); 
                    }
                }
                $('.btn-restart-{$action->id}').click(function() {
                    var form = $('#order-flow-action-form-{$action->id}');
                    form.trigger('reset.yiiActiveForm');
                    form.find('.warning-active').text('');
                    sendSms();
                    var file_id = form.find('.upload-file-id').val();
                    if(file_id !== '') $(".sure-btn-{$action->id}").removeAttr("disabled");
                });
                var canDisableSms = '{$canDisableSms}';
                if(canDisableSms != 0)
                {
                    $('.field-is_send_sms-{$action->id}').show();
                    $('#is_send_sms-{$action->id}').click(function(){
                        sendSms();
                    });
                }
                else
                {
                    $('.field-is_send_sms-{$action->id}').hide();
                }
                
                var inputIds = $inputIdsJson;
                var smsPreview = '{$smsPreview}';
                replaceSms{$action->id}(smsPreview, inputIds, '{$action_type}');
                if('{$action_type}' === '{$action_type_date}')
                {
                    $('#input_date-{$action->id}').keyup(function(){
                        replaceSms{$action->id}(smsPreview, inputIds, '{$action_type}', '{$hasSendVar}');
                    }).change(function(){
                        replaceSms{$action->id}(smsPreview, inputIds, '{$action_type}', '{$hasSendVar}');
                    });
                }
                var i;
                for(i in inputIds)
                {
                    $('#'+inputIds[i]).keyup(function(){
                        replaceSms{$action->id}(smsPreview, inputIds, '{$action_type}', '{$hasSendVar}');
                    });
                }
            });
            function replaceSms{$action->id}(smsPreview, inputIds, type, hasSendVar)
            {
                var startIndex = 1;
                if(hasSendVar === '1')
                {
                    startIndex = 4;
                }
                if(type === '{$action_type_date}')
                {
                    startIndex++;
                    smsPreview = smsPreview.replace('{1}', $('#input_date-{$action->id}').val());
                }
                for(var i = 0; i < inputIds.length; i++)
                {
                    smsPreview = smsPreview.replace('{'+(i+startIndex)+'}', $('#'+inputIds[i]).val());
                }
                $('#sms-preview-{$action->id}').text(smsPreview);
            }
            $('#order-flow-action-form-{$action->id}').on('beforeSubmit', function(){
                var form = $(this);
                $.post(form.attr('action'), form.serialize(), function(rs){
                    if(rs.status === 200)
                    {
                        form.trigger('reset.yiiActiveForm');
                        window.location.replace('{$url}');
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
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif;?>
<?php /*流程操作按钮弹框 end*/ ?>


<?php if($model->isRenewal() && $model->canRenewal()):?>
    <!--订单服务信息编辑弹框   start-->
    <div class="modal fade" id="order-edit-modal" role="dialog" aria-labelledby="modal-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">订单服务信息</h4>
                </div>
                <?php
                $orderInfoForm =new \backend\models\OrderInfoForm();
                $orderInfoForm->product_name = $model->product_name;
                $orderInfoForm->district_name = '';
                if (!empty($model->district_name))
                {
                    $orderInfoForm->district_name = ($model->province_name.'-'.$model->city_name.'-'.$model->district_name);
                }
                else
                {
                    $orderInfoForm->district_name = $model->service_area;
                }
                $orderInfoForm->price = $model->price;
                $orderInfoForm->sn = $model->sn;
                $orderInfoForm->created_at = Yii::$app->formatter->asDatetime($model->created_at);
                $orderInfoForm->payment_time = $model->virtualOrder->payment_time > 0 ? Yii::$app->formatter->asDatetime($model->virtualOrder->payment_time) : '';
                $orderInfoForm->service_cycle = $model->service_cycle;
                $orderInfoForm->begin_service_time = $model->begin_service_time > 0 ? Yii::$app->formatter->asDatetime($model->begin_service_time) : '';
                $orderInfoForm->begin_service_cycle = $model->begin_service_cycle > 0 ? Yii::$app->formatter->asDate($model->begin_service_cycle) : '';
                $orderInfoForm->end_service_cycle = $model->end_service_cycle > 0 ? Yii::$app->formatter->asDate($model->end_service_cycle) : '';
                $orderInfoForm->renewal_warn_time = $model->renewal_warn_time > 0 ? Yii::$app->formatter->asDate($model->renewal_warn_time) : '';
                $orderInfoForm->estimate_service_time = $model->estimate_service_time > 0 ? Yii::$app->formatter->asDate($model->estimate_service_time) : '';
                $form = \yii\bootstrap\ActiveForm::begin([
                    'action' => ['order/order-info-create', 'id'=> $model->id],
                    'validationUrl' => ['order/order-info-validation'],
                    'enableAjaxValidation' => true,
                    'id' => 'order-info-form',
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label' => 'col-md-4',
                            'offset' => 'col-md-offset-2',
                            'wrapper' => 'col-md-7 no-paddings',
                            'hint' => 'col-md-1 no-paddings',
                        ],
                    ]]);
                ?>
                <div class="modal-body input_box" style="padding: 0 90px;">
                    <div class="ibox-content" style="padding: 0;border: none;">
                        <fieldset class="form-horizontal">
                            <? Html::activeHiddenInput($orderInfoForm,'begin_service_time'); ?>
                            <div class="col-md-12" style="padding: 30px 0 0;">
                                <?= $form->field($orderInfoForm, 'service_cycle')->textInput(['readonly'=>'true'])->hint('个月');?>
                            </div>
                            <div class="col-md-12" style="padding: 30px 0 0;">
                                <?= $form->field($orderInfoForm, 'begin_service_cycle')->widget(DateTimePicker::className(), [
                                    'clientOptions' => [
                                        'format' => 'yyyy-mm-dd',
                                        'language' => 'zh-CN',
                                        'autoclose' => true,
                                        'minView' => 'month',
                                    ],
                                    'clientEvents' => [],
                                    'class'=>'col-sm-4',
                                ]);?>
                            </div>
                            <div class="col-md-12" style="padding: 30px 0 0;">
                                <?= $form->field($orderInfoForm, 'end_service_cycle')->widget(DateTimePicker::className(), [
                                    'clientOptions' => [
                                        'format' => 'yyyy-mm-dd',
                                        'language' => 'zh-CN',
                                        'autoclose' => true,
                                        'minView' => 'month',
                                    ],
                                    'clientEvents' => [],
                                    'class'=>'col-sm-4',
                                ]);?>
                            </div>
                            <div class="col-md-12" style="padding: 30px 0 0;">
                                <?= $form->field($orderInfoForm, 'renewal_warn_time')->widget(DateTimePicker::className(), [
                                    'clientOptions' => [
                                        'format' => 'yyyy-mm-dd',
                                        'language' => 'zh-CN',
                                        'autoclose' => true,
                                        'minView' => 'month',
                                    ],
                                    'clientEvents' => [],
                                    'class'=>'col-sm-4',
                                ]);?>
                            </div>
                            <div class="col-md-12" style="padding: 30px 0 0;">
                                <?= $form->field($orderInfoForm, 'estimate_service_time')->widget(DateTimePicker::className(), [
                                    'clientOptions' => [
                                        'format' => 'yyyy-mm-dd',
                                        'language' => 'zh-CN',
                                        'autoclose' => true,
                                        'minView' => 'month',
                                    ],
                                    'clientEvents' => [],
                                    'class'=>'col-sm-4',
                                ]);?>
                            </div>
                        </fieldset>
                    </div>
                    <?php
                    $this->registerJs(<<<JS
                    $('#orderinfoform-begin_service_cycle').change(function(){
                        var cycleDate = parseInt($('#orderinfoform-service_cycle').val());
                        var date = new Date($(this).val());
                        var day = date.getDate();
                        var endTime = getMonthBeforeFormatAndDay(date, cycleDate, '-', day);
                        if(!$(this).val())
                        {
                            endTime = '';
                        }
                        $('#orderinfoform-end_service_cycle').val(endTime);
                    });
                    $('#orderinfoform-end_service_cycle').change(function(){
                        var cycleDate = parseInt($('#orderinfoform-service_cycle').val());
                        var date = new Date($(this).val());
                        var day = date.getDate();
                        var beginTime = getMonthBeforeFormatAndDay(date, -cycleDate, '-', day);
                        if(!$(this).val())
                        {
                            beginTime = '';
                        }
                        $('#orderinfoform-begin_service_cycle').val(beginTime);
                    });
                    //求自然月日期
                    function getMonthBeforeFormatAndDay(time,num, format, day){
                        var date = new Date(time);
                        date.setMonth(date.getMonth() + (num*1), 1);
                        //读取日期自动会减一，所以要加一
                        var mo = date.getMonth() + 1;
                        //小月
                        if(mo == 4 || mo == 6 || mo == 9 || mo == 11){
                            if(day > 30){
                                day = 30
                            }
                        }
                        //2月
                        else if(mo == 2){
                            if(isLeapYear(date.getFullYear())){
                                if(day > 29){
                                    day = 29
                                }else{
                                    day = 28
                                }
                            }
                            if(day > 28){
                                day = 28
                            }
                        }
                        //大月
                        else{
                            if(day > 31){
                                day = 31
                            }
                        }
                        if(day < 10){
                            day = "0" + day;
                        }
                        var relValue = date.format('yyyy' + format + 'MM' + format + day);
                        return relValue;
                    }
            
                    //JS判断闰年代码
                    function isLeapYear(Year){
                        if(((Year % 4) == 0) && ((Year % 100) != 0) || ((Year % 400) == 0)){
                            return true;
                        }else{
                            return false; 
                        }
                    }
                    Date.prototype.format = function(fmt){ 
                         var o = { 
                            "M+" : this.getMonth()+1,                 //月份 
                            "d+" : this.getDate(),                    //日 
                            "h+" : this.getHours(),                   //小时 
                            "m+" : this.getMinutes(),                 //分 
                            "s+" : this.getSeconds(),                 //秒 
                            "q+" : Math.floor((this.getMonth()+3)/3), //季度 
                            "S"  : this.getMilliseconds()             //毫秒 
                        }; 
                        if(/(y+)/.test(fmt)){
                                fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length)); 
                        }
                        for(var k in o){
                            if(new RegExp("("+ k +")").test(fmt)){
                            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
                            }
                        }
                        return fmt; 
                    } 
JS
                    ) ?>
                </div>
                <div class="modal-footer" style="border: none;text-align: center;padding: 30px 0 50px 0;">
                    <button type="submit" class="btn btn-primary" style="width: 120px;height: 34px;">保存</button>
                </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
            </div>
        </div>
    </div>
<?php endif;?>

    <div class="modal fade" id="image-modal" role="dialog" aria-labelledby="image-modal-label">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img src="" >
                </div>
            </div>
        </div>
    </div>

<?php
$this->registerJs(<<<JS
    $('.review-btn').click(function(){
        var model = $('#image-modal');
        model.find('.modal-body').empty();
        var image = $(this).attr('data-image');
        model.find('.modal-body').append($('<img >').attr('src', image).attr('width','100%'));
    });
JS
) ?>