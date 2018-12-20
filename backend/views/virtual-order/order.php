<?php
/** @var $this \yii\web\View */
/* @var $provider \yii\data\ActiveDataProvider */

use backend\models\OrderReplaceTeamForm;
use backend\widgets\LinkPager;
use common\models\BusinessSubject;
use common\utils\Decimal;
use imxiangli\select2\Select2Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use zhuravljov\yii\widgets\DateTimePicker;

/** @var $model \common\models\VirtualOrder */
/** @var $status int  */
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
$companyOptions = ['class' => 'form-control', 'prompt'=>'请选择公司'];
$departmentOptions = ['class' => 'form-control', 'prompt'=>'请选择业务员'];

/** @var \common\models\Order[] $models  */
$models = $provider->getModels();
$pagination = $provider->getPagination();
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
                                            <a href="<?= Url::to(['virtual-order/order','vid' => $model->id,'status' => 'pending-payment']) ?>" class="<?php if($status == 'pending-payment'): ?>payment-button-active<?php endif; ?>">待付款</a>
                                            <a href="<?= Url::to(['virtual-order/order','vid' => $model->id,'status' => 'unpaid']) ?>" class="<?php if($status == 'unpaid'): ?>payment-button-active<?php endif; ?>">未付清</a>
                                            <a href="<?= Url::to(['virtual-order/order','vid' => $model->id,'status' => 'already-payment']) ?>" class="<?php if($status == 'already-payment'): ?>payment-button-active<?php endif; ?>">已付款</a>
                                        </div>
                                    </div>
                                    <div class="batch-modifying">
                                        <div class="clearfloat">
                                            <?php if (Yii::$app->user->can('virtual-order-action/payment-mode') && ($status == 'unpaid' || $status == 'already-payment')): ?>
                                            <button class="btn btn-primary payment-btn" data-target="#payment-mode-modal" data-toggle="modal">批量修改付款方式</button>
                                            <?php endif; ?>
                                            <?php if (Yii::$app->user->can('virtual-order-action/batch-adjust-price') && $status == 'pending-payment'): ?>
                                                <button class="btn btn-primary pending-payment-btn" data-target="#payment-mode-modal" data-toggle="modal">批量修改付款方式</button>
                                                <button class="btn btn-primary batch-adjust-price-btn" data-target="#batch-adjust-order-price-modal" data-toggle="modal" data-id="" data-original-price="<?= $model->total_amount; ?>">批量修改订单价格</button>
                                            <?php endif; ?>
                                            <?php if (Yii::$app->user->can('virtual-order-action/change-financial')): ?>
                                            <button class="btn btn-primary financial-code-btn" data-target="#financial-code-modal" data-toggle="modal">批量编辑财务明细编号</button>
                                            <?php endif; ?>
                                            <?php if (Yii::$app->user->can('virtual-order-action/change-settlement-month')): ?>
                                                <button class="btn btn-primary settlement-month-btn" data-target="#settlement-month-modal" data-toggle="modal">批量编辑订单业绩提点月</button>
                                            <?php endif; ?>
                                            <?php if (Yii::$app->user->can('virtual-order-action/knot-expected-cost')): ?>
                                                <button class="btn btn-primary batch-expected-cost-btn" data-target="#batch-expected-cost-modal" data-toggle="modal">批量结转预计利润</button>
                                            <?php endif; ?>
                                            <?php if (Yii::$app->user->can('virtual-order-action/replace-order-team')): ?>
                                            <button class="btn btn-primary replace-salesman-team-btn" data-target="#replace-salesman-team-modal" data-toggle="modal">批量替换订单共享业务员</button>
                                            <?php endif; ?>
                                            <?php if (Yii::$app->user->can('virtual-order-action/replace-order-salesman')): ?>
                                            <button class="btn btn-primary batch-change-salesman-btn" data-target="#batch-change-salesman-modal" data-toggle="modal">批量替换订单负责业务员</button>
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
		                                                <?php if (Yii::$app->user->can('order-action/change-salesman')): ?>
		                                                    <button class="btn btn-xs btn-link text-info change-salesman-btn"
		                                                            data-target="#change-salesman-modal"
		                                                            data-toggle="modal" data-sn="<?= $order->sn ?>"
		                                                            data-user-id="<?= $order->user->id; ?>" data-id="<?= $order->id ?>">修改
		                                                    </button>
		                                                <?php endif; ?>
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
	                                                    <?php if (Yii::$app->user->can('order-action/change-salesman')): ?>
	                                                        <div class="team-btn<?= $order->id ?>">
	                                                            <button class="btn btn-xs btn-link text-info change-salesman-team-btn"
	                                                                    data-target="#change-salesman-team-modal"
	                                                                    data-toggle="modal"
	                                                                    data-id="<?= $order->id ?>"
	                                                                    data-sn="<?= $order->sn ?>"
	                                                                    data-salesman="<?= $order->salesman_name; ?>"
	                                                                    data-rate="<?= $order->getDivideRate(); ?>"
	                                                                    data-department="<?= $order->salesmanDepartment ? $order->salesmanDepartment->name : null; ?>"
	                                                                    data-user-id="<?= $order->user->id; ?>">修改
	                                                            </button>
	                                                        </div>
	                                                    <?php endif; ?>
	                                                <?php endif; ?>
	                                            </td>

	                                            <!-- 客服人员 -->
	                                            <td style="vertical-align: middle;">
	                                                <?= $order->customer_service_name; ?>
	                                                <?php if (Yii::$app->user->can('order-action/change-customer-service') && ($order->salesman_aid || $order->is_vest)): ?>
	                                                    <?php if ($order->isPendingPay() || $order->isPendingAllot() || $order->isPendingService() || $order->isInService()): ?>
	                                                        <!--不同的状态下的客服状态-->
	                                                        <button class="btn btn-xs btn-link text-info customer_service-btn"
	                                                                data-target="#customer-service-modal"
	                                                                data-toggle="modal"
	                                                                data-id="<?= $order->id ?>">修改
	                                                        </button>
	                                                    <?php endif; ?>
	                                                <?php endif; ?>
	                                            </td>

	                                            <!-- 服务人员 -->
	                                            <td style="vertical-align: middle;">
	                                                <?= $order->clerk_name; ?>
	                                                <?php if (Yii::$app->user->can('order-action/change-clerk') && ($order->customer_service_id || $order->is_vest)): ?>
	                                                    <?php if ($order->isPendingService() || $order->isInService()): ?>
	                                                        <!--不同的状态下的服务人员状态-->
	                                                        <button class="btn btn-xs btn-link text-info clerk-btn"
	                                                                data-target="#clerk-modal"
	                                                                data-toggle="modal" data-id="<?= $order->id ?>"
	                                                                data-product-id="<?= $order->product_id ?>"
	                                                                data-district-id="<?= $order->district_id ?>">修改
	                                                        </button>
	                                                    <?php endif; ?>
	                                                <?php endif; ?>
	                                            </td>

	                                            <!-- 付款方式 -->
	                                            <td style="vertical-align: middle;">
	                                                <p><?= $order->is_installment ? '分期付款' : '一次付款'; ?></p>
                                                    <?php if (Yii::$app->user->can('virtual-order-action/payment-mode') && $status == 'unpaid'): ?>
                                                        <button class="btn btn-xs btn-default update-pay-mode" data-is_installment="<?= $order->is_installment ?>"
                                                                data-id="<?= $order->id ?>" data-target="#payment-mode-modal" data-toggle="modal">修改付款方式</button>
                                                    <?php endif; ?>
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
	                                                <p <?php if ($order->getPendingPayAmount()): ?>class="text-danger"<?php else:?>class="text-primary"<?php endif; ?>>
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

	                                            <!--操作-->
	                                            <td style="vertical-align: middle; text-align: center;">
                                                    <?php if (Yii::$app->user->can('follow-record/create')
                                                        && ($model->isPendingPayment() || ($model->isUnpaid() && $order->isUnpaid() && !$order->is_installment)
                                                            || ($model->isCanceled() && $model->hasFollowRecords()))): ?>
	                                                <span class="btn btn-xs btn-primary see-order-follow-record m-t-xs"
	                                                      data-target="#order-follow-record-modal"
	                                                      data-toggle="modal"
	                                                      data-is-cancel="<?= $model->isCanceled() ? '1' : '0'?>"
	                                                      data-order-id="<?= $order->id; ?>"
	                                                      data-id="<?= $order->virtual_order_id ?>">跟进记录</span>
	                                            <?php endif; ?>
	                                            <?php if (Yii::$app->user->can('virtual-order-action/allot-price') && $order->payment_amount !== $order->price): ?>
	                                                <button class="btn btn-xs btn-warning allocation-price-btn m-t-xs"
	                                                    data-id="<?= $order->id; ?>"
	                                                    data-order-total-amount="<?= $order->getTotalAmount(); ?>"
	                                                    data-order-payment_amount="<?= $order->payment_amount; ?>"
	                                                    data-v-total_amount="<?= $model->total_amount; ?>"
	                                                    data-v-payment_amount="<?= $model->payment_amount; ?>"
	                                                    data-target="#allocation-price-modal" data-toggle="modal">分配回款</button>
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

<!--批量结算预计利润start-->
<?php if (Yii::$app->user->can('virtual-order-action/knot-expected-cost')): ?>
<div class="modal fade" id="batch-expected-cost-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $batchKnotExpectedCost = new \backend\models\BatchKnotExpectedProfitForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['virtual-order-action/knot-expected-profit'],
        'id' => 'knot-expected-profit-form',
        'validationUrl' => ['virtual-order-action/knot-expected-profit', 'is_validate' => 1],
        'enableAjaxValidation' => true,
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-3',
                'offset' => 'col-sm-offset-2',
                'wrapper' => 'col-sm-7',
                'hint' => 'col-sm-2'
            ],
        ],
    ]); ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">批量结转预计利润</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($batchKnotExpectedCost, 'company_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                    'serverUrl' => \yii\helpers\Url::to(['company/ajax-list']),
                    'itemsName' => 'company',
                    'placeholderId' => '0',
                    'width' => '300px',
                    'placeholder' => '请选择公司',
                    'searchKeywordName' => 'keyword',
                    'eventSelect' => new JsExpression("
                     $('#batchknotexpectedprofitform-department_id').val('0').trigger('change');
                                ")
                ])->label('所属公司');
                $companyUrl = \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']);
                echo $form->field($batchKnotExpectedCost, 'department_id')->widget(Select2Widget::className(), [
                    'selectedItem' => [],
                    'options' => $departmentOptions,
                    'placeholderId' => '0',
                    'width' => '300px',
                    'placeholder' => '请选择部门',
                    'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']),
                    'itemsName' => 'department',
                    'eventOpening' => new JsExpression("
                        var id = $('#batchknotexpectedprofitform-company_id').val();
                        serverUrl = '{$companyUrl}'.replace('__company_id__', id ? id : '-1');
                    ")
                ])->label('所属部门');?>
                <?= $form->field($batchKnotExpectedCost, 'rate')->textInput()->hint('%'); ?>
            </div>
            <div class="modal-footer">
                <?= Html::activeHiddenInput($batchKnotExpectedCost, 'order_id'); ?>
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary sure-btn">确定</button>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>
    <?php
    $this->registerJs(<<<JS
$(function() 
{
    var form = $('#knot-expected-profit-form');
    $('.batch-expected-cost-btn').click(function() 
    {
        var order_ids = checkedValues();
        $('#batchknotexpectedprofitform-order_id').val(order_ids);
        $('#batchknotexpectedprofitform-company_id').val('0').trigger('change');
        $('#batchknotexpectedprofitform-department_id').val('0').trigger('change');
        form.trigger('reset.yiiActiveForm');
        form.find('.warning-active').empty();
    })

    form.on('beforeSubmit', function()
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
    });
})


JS
    ) ?>
</div>
<?php endif; ?>
<!--批量结算预计利润end-->

<!--批量替换负责业务员start-->
<?php if (Yii::$app->user->can('virtual-order-action/replace-order-salesman')): ?>
<div class="modal fade" id="batch-change-salesman-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $batchChangeSalesmanForm = new \backend\models\BatchChangeSalesmanForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['virtual-order-action/batch-change-salesman'],
        'id' => 'batch-change-salesman-form',
        'validationUrl' => ['virtual-order-action/batch-change-salesman', 'is_validate' => 1],
        'enableAjaxValidation' => true,
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-3',
                'offset' => 'col-sm-offset-2',
                'wrapper' => 'col-sm-7',
                'hint' => 'col-sm-2'
            ],
        ],
    ]); ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">批量替换订单负责业务员</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($batchChangeSalesmanForm, 'company_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                    'serverUrl' => \yii\helpers\Url::to(['company/ajax-list']),
                    'itemsName' => 'company',
                    'placeholderId' => '0',
                    'width' => '390px',
                    'placeholder' => '请选择公司',
                    'searchKeywordName' => 'keyword',
                    'eventSelect' => new JsExpression("
                           $('#batchchangesalesmanform-administrator_id').val('0').trigger('change');
                            ")
                ])->label('所属公司');
                $ajaxCompanyUrl = \yii\helpers\Url::to(['administrator/ajax-list', 'type' => \common\models\Administrator::TYPE_SALESMAN, 'company_id' => '__company_id__']);

                echo $form->field($batchChangeSalesmanForm, 'administrator_id')->widget(Select2Widget::className(), [
                    'selectedItem' => [],
                    'options' => $departmentOptions,
                    'placeholderId' => '0',
                    'width' => '390px',
                    'placeholder' => '请选择业务员',
                    'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-personnel-list', 'company_id' => '__company_id__']),
                    'itemsName' => 'items',
                    'eventOpening' => new JsExpression("
                    var id = $('#batchchangesalesmanform-company_id').val();
                    serverUrl = '{$ajaxCompanyUrl}'.replace('__company_id__', id != 0 ? id : '-1');
                ")
                ])->label('所属业务员');?>
            </div>
            <div class="modal-footer">
                <?= Html::activeHiddenInput($batchChangeSalesmanForm, 'order_id'); ?>
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary sure-btn">确定</button>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>
    <?php
    $this->registerJs(<<<JS
    $(function() 
    {
        var form = $('#batch-change-salesman-form');
        $('.batch-change-salesman-btn').click(function() 
        {
            var order_ids = checkedValues();
            $('#batchchangesalesmanform-order_id').val(order_ids);
            $('#batchchangesalesmanform-company_id').val('0').trigger('change');
            $('#batchchangesalesmanform-administrator_id').val('0').trigger('change');
            form.trigger('reset.yiiActiveForm');
            form.find('.warning-active').empty();
        })

        form.on('beforeSubmit', function()
        {
            var form = $('#batch-change-salesman-form');
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
</div>
<?php endif; ?>
<!--批量替换负责业务员end-->

<!--批量替换共享业务员start-->
<?php if (Yii::$app->user->can('virtual-order-action/replace-order-team')): ?>
    <div class="modal fade" id="replace-salesman-team-modal" role="dialog" aria-labelledby="modal-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">批量替换订单共享业务员</h4>
                </div>
                <div class="modal-body input_box">
                    <?php
                    $orderReplaceTeamForm = new \backend\models\OrderReplaceTeamForm();
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'id' => 'order-replace-team-form',
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-3',
                                'offset' => 'col-sm-offset-2',
                                'wrapper' => 'col-sm-7',
                                'hint' => 'col-sm-2'
                            ],
                        ],
                    ]); ?>
                    <?= $form->field($orderReplaceTeamForm, 'company_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                        'serverUrl' => \yii\helpers\Url::to(['company/ajax-list']),
                        'itemsName' => 'company',
                        'placeholderId' => '0',
                        'width' => '390px',
                        'placeholder' => '请选择公司',
                        'searchKeywordName' => 'keyword',
                        'eventSelect' => new JsExpression("
                               $('#orderreplaceteamform-admin_id').val('0').trigger('change');
                                ")
                    ])->label('所属公司');
                    $ajaxCompanyUrl = \yii\helpers\Url::to(['administrator/ajax-personnel-list', 'type' => \common\models\Administrator::TYPE_SALESMAN, 'company_id' => '__company_id__']);

                    echo $form->field($orderReplaceTeamForm, 'admin_id')->widget(Select2Widget::className(), [
                        'selectedItem' => [],
                        'options' => $departmentOptions,
                        'placeholderId' => '0',
                        'width' => '390px',
                        'placeholder' => '请选择业务员',
                        'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-personnel-list', 'company_id' => '__company_id__']),
                        'itemsName' => 'items',
                        'eventOpening' => new JsExpression("
                        var id = $('#orderreplaceteamform-company_id').val();
                        serverUrl = '{$ajaxCompanyUrl}'.replace('__company_id__', id != 0 ? id : '-1');
                    "),
                        'eventSelect' => new JsExpression("
                        selectedSalesman = env.params.data;
                        console.log(env.params.data);
                    ")
                    ])->label('所属业务员');?>
                    <div class="form-group">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-7">
                            <button type="button" class="btn btn-primary sure-btn add-item-confirm">添加保存</button>
                        </div>
                    </div>
                    <?php \yii\bootstrap\ActiveForm::end(); ?>
					<div class="replace-salesman-team-border"></div>
                    <?php
                    $index = 0;
                    $item = new OrderReplaceTeamForm();
                    $input_rate = Html::activeTextInput($item, '[salesman_admin][{index}]rate', ['class' => 'form-control text-right', 'id' => false]);
                    $hidden_input_admin_id = Html::activeHiddenInput($item, '[salesman_admin][{index}]admin_id', ['id' => false,'value' => '{val_admin_id}']);
                    $line = str_replace(["\n", "\r", "\n\r"], ' ', '<tr data-id="{id}">
                <td style="vertical-align: middle;" class="text-center">共享人</td>
                <td style="vertical-align: middle;" class="text-center">{admin_name}{hidden_input_admin_id}</td>
                <td style="vertical-align: middle;" width="100px" class="text-center">{admin_department}</td>
                <td style="vertical-align: middle;" class="text-center">{input_rate}</td>
                <td style="vertical-align: middle;" class="text-center">
                    <button type="button" class="btn btn-xs btn-danger delete-product">删除</button>
                </td>
            </tr>');
                    $ajaxCompanyUrl = \yii\helpers\Url::to(['administrator/ajax-list', 'type' => \common\models\Administrator::TYPE_SALESMAN, 'company_id' => '__company_id__']);
                    $orderReplaceTeam = new OrderReplaceTeamForm();
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'action' => ['virtual-order-action/replace-order-team'],
                        'id' => 'order-replace-form',
                        'validationUrl' => ['virtual-order-action/allot-price', 'is_validate' => 1],
                        'enableAjaxValidation' => true,
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-3',
                                'offset' => 'col-sm-offset-2',
                                'wrapper' => 'col-sm-7',
                                'hint' => 'col-sm-2'
                            ],
                        ],
                    ]); ?>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center">角色</th>
                                    <th class="text-center">姓名</th>
                                    <th class="text-center">部门</th>
                                    <th class="text-center">业绩分配比例</th>
                                    <th class="text-center">操作</th>
                                </tr>
                                </thead>
                                <tbody id="order-items">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?= Html::activeHiddenInput($orderReplaceTeam, 'order_id'); ?>
                    <div class="form-group">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-8">
                            <button type="submit" class="btn btn-primary sure-btn">确定</button>
                            <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                        </div>
                    </div>
                    <?php \yii\bootstrap\ActiveForm::end(); ?>
                </div>
                <div class="modal-footer replace-order-team">
                    <span class="text-danger warning-active"></span>
                </div>
        </div>
    </div>
<?php
$this->registerJs(<<<JS

        var index = parseInt('{$index}');
        var selectedSalesman = null;
        var line = '{$line}';
        var hidden_input_admin_id = '{$hidden_input_admin_id}';
        var input_rate = '{$input_rate}';
        var add_form = $('#order-replace-team-form');
        $(function() 
        {
            $('.replace-salesman-team-btn').click(function() 
            {
                var order_ids = checkedValues();
                $('#orderreplaceteamform-order_id').val(order_ids);
                $('#order-items').empty();
                $('#orderreplaceteamform-company_id').val('0').trigger('change');
                $('#orderreplaceteamform-admin_id').val('0').trigger('change');
                $('.replace-order-team').find('.warning-active').text('');
            })
            $('.add-item-confirm').click(function()
            {
                var indexStr = ""+(++index);
                if(null === selectedSalesman)
                {
                    add_form.find('.warning-active').text('请添加共享业务员');
                    return false;
                }
                var input_admin_id = hidden_input_admin_id.replace('{val_admin_id}', selectedSalesman.id).replace('{index}', indexStr);
                var rate = input_rate.replace('{index}', indexStr);
                $('#order-items').append($(line
                .replace('{hidden_input_admin_id}', input_admin_id)
                .replace('{input_rate}', rate)
                .replace('{admin_name}', selectedSalesman['name'])
                .replace('{admin_department}', selectedSalesman['d_name'])
                ));
                selectedSalesman = null;
                $('#orderreplaceteamform-company_id').val('0').trigger('change');
                $('#orderreplaceteamform-admin_id').val('0').trigger('change');
            });
            
            $('#order-items').delegate('tr td button.delete-product','click',function()
            {
                $(this).parents('tr').remove();
            });
            
            $('#order-replace-form').on('beforeSubmit', function()
            {
                var form = $('#order-replace-form');
                $.post(form.attr('action'), form.serialize(), function(rs)
                {
                    if(rs.status === 200)
                    {
                        form.trigger('reset.yiiActiveForm');
                        window.location.reload();
                    }
                    else
                    {
                        $('.replace-order-team').find('.warning-active').text(rs.message);
                    }
                }, 'json');
                return false;
            });
        })
        

JS
) ?>
    </div>
<?php endif; ?>
<!--批量替换共享业务员end-->

<!--分配回款start-->
<?php if (Yii::$app->user->can('virtual-order-action/allot-price')): ?>
<div class="modal fade" id="allocation-price-modal" role="dialog" aria-labelledby="allocation-price-label">
    <?php
    $allocationPriceForm = new \backend\models\AllocationPriceForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['virtual-order-action/allot-price'],
        'id' => 'allot-price-form',
        'validationUrl' => ['virtual-order-action/allot-price', 'is_validate' => 1],
        'enableAjaxValidation' => true,
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-4',
                'wrapper' => 'col-sm-7',
                'hint' => 'col-sm-12 col-sm-offset-5',
            ],
        ],
    ]);
    ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="allocation-price-label">分配回款</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($allocationPriceForm, 'total_amount')->staticControl() ?>
                <?= $form->field($allocationPriceForm, 'payment_amount')->staticControl() ?>
                <?= $form->field($allocationPriceForm, 'allot_price')->textInput() ?>
                <div class="form-group field-batchadjustpriceform-adjust_price">
                    <label class="control-label col-sm-4"></label>
                    <div class="col-sm-7">（需输入数字，+50为增加50元，-50为减少50元；且分配金额为该子订单的已付金额；分配金额应小于虚拟订单可分配回款金额。）</div>
                    <div class="help-block col-sm-12 col-sm-offset-3"></div>
                </div>
                <?= $form->field($allocationPriceForm, 'allot_payment_amount')->staticControl() ?>
                <?= $form->field($allocationPriceForm, 'order_payment_amount')->staticControl() ?>
                <?= $form->field($allocationPriceForm, 'allot_price_reason')->textarea() ?>
            </div>
            <div class="modal-footer">
                <?= Html::activeHiddenInput($allocationPriceForm, 'order_id'); ?>
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary sure-btn">确定</button>
            </div>
        </div>
    </div>
    <?php
    $ajaxGetAdjustInfoUrl = \yii\helpers\Url::to(['order-action/get-adjust-info']);
    $this->registerJs(<<<JS
    $(function() 
    {
        var form = $('#allot-price-form');
        var order_payment_amount = 0;
        function changeAdjustPrice()
        {
            var allotPrice = parseFloat(form.find('#allocationpriceform-allot_price').val());
            if(isNaN(allotPrice)) allotPrice = 0;
            var price = parseFloat(order_payment_amount) + allotPrice;
            form.find('.field-allocationpriceform-allot_payment_amount .form-control-static').text(fmoney(price));
        }
        $('.allocation-price-btn').on('click', function()
        {
            form.find('.warning-active').text('');
            var id = $(this).attr('data-id');
            form.trigger('reset.yiiActiveForm');
            $('#allocationpriceform-order_id').val(id);
            var order_total_amount = $(this).attr('data-order-total-amount');
            order_payment_amount = $(this).attr('data-order-payment_amount');
            var payment_amount = $(this).attr('data-v-payment_amount');
            var remainder = parseFloat(payment_amount) - parseFloat(order_total_amount);//剩余可用
            form.find('.field-allocationpriceform-total_amount .form-control-static').text(fmoney(payment_amount));
            form.find('.field-allocationpriceform-payment_amount .form-control-static').text(fmoney(remainder));
            form.find('.field-allocationpriceform-order_payment_amount .form-control-static').text(fmoney(order_payment_amount));
            changeAdjustPrice();
        });
        form.find('#allocationpriceform-allot_price').change(function()
        {
            changeAdjustPrice();
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
<?php endif; ?>
<!--分配回款end-->

<!--修改客服start-->
<?php if (Yii::$app->user->can('order-action/change-customer-service')): ?>
    <div class="modal fade" id="customer-service-modal" role="dialog" aria-labelledby="modal-title">
        <?php
        $changeOrderCustomerServiceForm = new \backend\models\ChangeOrderCustomerServiceForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['order-action/change-customer-service'],
            'id' => 'customer-service-form',
            'validationUrl' => ['order-action/change-customer-service', 'is_validate' => 1],
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">修改客服</h4>
                </div>
                <div class="modal-body input_box customer-service-div">
                    <div class="table-responsive change-customer-service-table">
                        <table class="table table-striped ">
                            <thead>
                            <tr>
                                <th>选择</th>
                                <th>姓名</th>
                                <th>服务中单数</th>
                                <th>手机号</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($changeOrderCustomerServiceForm, 'order_id', ['id' => 'change-customer-service-form_order_id']); ?>
                    <span class="text-danger warning-active"><?= Html::error($changeOrderCustomerServiceForm, 'customer_service_id'); ?></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary customer_service-sure-btn">保存</button>
                </div>
            </div>
        </div>
        <?php
        $ajaxCustomerServiceListUrl = \yii\helpers\Url::to(['customer-service/ajax-list']);
        $changeCustomerServiceTemplate = '<tr><td><input name="customer_service_id" type="radio" value="{id}" /></td><td>{name}</td><td>{servicing_number}</td><td>{phone}</td></tr>';
        $this->registerJs(<<<JS
            $('.customer_service-btn').click(function(){
                $('.change-customer-service-table table tbody').empty();
                var changeCustomerServiceTemplate = '{$changeCustomerServiceTemplate}';
                $('#change-customer-service-form_order_id').val($(this).attr('data-id'));
                var order_id = $(this).attr('data-id');
                $.get('{$ajaxCustomerServiceListUrl}',{order_id:order_id},function(rs){
                    if(rs.status === 200){
                        for(var i = 0;i < rs['model'].length; i++) {
                        var item = changeCustomerServiceTemplate.replace('{id}', rs['model'][i]['id']).replace('{servicing_number}', rs['model'][i]['servicing_number']).replace('{phone}', rs['model'][i]['phone']).replace('{name}', rs['model'][i]['name']);
                        $('.change-customer-service-table table tbody').append(item);
                      }
                    }
                },'json');
            });
            $('#customer-service-form').on('beforeSubmit', function(){
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
<!--修改客服end-->

<!--修改订单业务人员start-->
<?php if (Yii::$app->user->can('order-action/change-salesman')): ?>
    <div class="modal fade" id="change-salesman-modal" role="dialog" aria-labelledby="modal-title">
        <?php
        $changeSalesmanUrl = Url::to(['administrator/ajax-salesman-list','order_id' => '__order_id__']);
        $changeSalesmanForm = new \backend\models\ChangeSalesmanForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['order-action/change-salesman'],
            'id' => 'change-salesman-form',
            'validationUrl' => ['order-action/change-salesman', 'is_validate' => 1],
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">修改业务人员</h4><span style="color: red">请慎重选择业务员，保存之后将影响预计利润和业绩结算</span>
                </div>
                <div class="modal-body input_box customer-service-div">
                    <?=
                    $form->field($changeSalesmanForm, 'salesman_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                        'serverUrl' => ['administrator/ajax-salesman-list','order_id'=>'__order_id__'],
                        'itemsName' => 'items',
                        'nameField' => 'name',
                        'searchKeywordName' => 'keyword',
                        'width' => '160',
                        'eventOpening' => new JsExpression("
                                        var order_id = $('#change-salesman-form_order_id').val();
                                        serverUrl = '{$changeSalesmanUrl}'.replace('__order_id__', order_id ? order_id : '-1');
                                        $('.warning-active').text('');
                        ")
                    ]); ?>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($changeSalesmanForm, 'order_id', ['id' => 'change-salesman-form_order_id']); ?>
                    <?= Html::activeHiddenInput($changeSalesmanForm, 'user_id', ['id' => 'change-salesman-form_user_id']); ?>
                    <span class="text-danger warning-active"><?= Html::error($changeSalesmanForm, 'salesman_id'); ?></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary customer_service-sure-btn">保存</button>
                </div>
            </div>
        </div>
        <?php
        $ajaxSalesmanListUrl = \yii\helpers\Url::to(['salesman/ajax-list']);
        $changeSalesmanTemplate = '<tr><td><input name="salesman_id" type="radio" value="{id}" /></td><td>{name}</td><td>{phone}</td></tr>';
        $this->registerJs(<<<JS
            $('.change-salesman-btn').click(function(){
                $('#change-salesman-form_order_id').val($(this).attr('data-id'));
                $('#change-salesman-form_user_id').val($(this).attr('data-user-id'));
            });
            $('#change-salesman-form').on('beforeSubmit', function(){
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
<!--修改订单业务人员end-->

<!--修改订单分成业务人员start-->
<?php if (Yii::$app->user->can('order-action/change-salesman')): ?>
    <div class="modal fade" id="change-salesman-team-modal" role="dialog" aria-labelledby="modal-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">业务人员修改</h4>
                    <span style="color: red">请慎重选择业务员，保存之后将影响预计利润和业绩结算</span>
                </div>
                <div class="modal-body input_box">
                    <?php
                    $salesmanUrl = Url::to(['administrator/ajax-salesman-list','order_id' => '__order_id__']);
                    $orderTeamForm = new \backend\models\OrderTeamForm();
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'id' => 'order-team-form',
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-4',
                                'offset' => 'col-sm-offset-2',
                                'wrapper' => 'col-sm-4',
                                'hint' => 'col-sm-2'
                            ],
                        ],
                    ]); ?>
                    <?= Html::activeHiddenInput($orderTeamForm,'order_id') ?>
                    <?=
                    $form->field($orderTeamForm, 'administrator_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                        'serverUrl' => ['administrator/ajax-salesman-list','order_id'=>'__order_id__'],
                        'itemsName' => 'items',
                        'nameField' => 'name',
                        'searchKeywordName' => 'keyword',
                        'width' => '160',
                        'eventOpening' => new JsExpression("
                                        var order_id = $('#order_id').val();
                                        serverUrl = '{$salesmanUrl}'.replace('__order_id__', order_id ? order_id : '-1');
                                        $('.salesman-err').text('');
                        ")
                    ]); ?>
                    <?= $form->field($orderTeamForm, 'divide_rate')->textInput()->hint('%')?>
                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-4">
                            <span class="text-danger warning-active salesman-err"></span><br>
                            <button type="button" class="btn btn-default team-salesman-btn" id="btn-use">添加</button>
                        </div>
                    </div>
                    <?php \yii\bootstrap\ActiveForm::end(); ?>
                </div>
                <div class="modal-footer">
                    <form id="rote-form">
                        <input type="hidden" name="order_id" id="rote_form_order_id">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>人员</th>
                                <th>部门</th>
                                <th>业绩分配比例</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="team-list">
                            </tbody>
                        </table>
                        <span class="text-danger rate-error-text"></span>
                        <button type="button" class="btn btn-primary save-rate-btn">保存分配比例</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        $changeSalesmanUrl = Url::to(['order-action/create-order-team']);//多业务员
        $changeRateUrl = Url::to(['order-action/change-order-team-rate']);//更改分成比例
        $orderTeamUrl = Url::to(['order-team/ajax-list']);
        $deleteTeamUrl = Url::to(['order-team/delete']);
        $this->registerJs(<<<JS
            $('#divide_rate').focus(function() 
            {
               $('.salesman-err').text('');
            })
            $('.change-salesman-team-btn').click(function(){
                var order_id = $(this).attr('data-id');
                var salesman = $(this).attr('data-salesman');
                var department = $(this).attr('data-department');
                var rate = $(this).attr('data-rate');
                $('#order_id').val(order_id);
                $('#rote_form_order_id').val(order_id);
                var data = 
                '<tr>'+
                '<th>'+salesman+'</th>'+
                '<th>'+department+'</th>'+
                '<th class="order_team_rate">'+rate+'%</th>'+
                '<th></th>'+
                '</tr>';
                $('#team-list').empty().html(data);
                $.get('{$orderTeamUrl}',{order_id:order_id},function(rs)
                {
                    if(rs.status === 200)
                    {
                        for(var i = 0;i < rs['models'].length; i++) 
                        {   var  result = null;
                            result = 
                             '<tr class="list-item" data-id="'+rs['models'][i]['id']+'" data-rate="'+rs['models'][i]['divide_rate']+'">'+
                             '<th class="cost-list-name">'+rs['models'][i]['administrator_name']+'</th>'+
                             '<th>'+rs['models'][i]['department_name']+'</th>'+
                             '<th>'+
                             '<input type="text" class="cls_rate" name="rate[]" value="'+rs['models'][i]['divide_rate']+'">%'+
                             '<input type="hidden" name="team[]" value="'+rs['models'][i]['id']+'">' +
                             '</th>'+
                             '<th>'+
                             '<span class="btn btn-xs btn-white del-team-btn">删除</span>' +
                             '</th>'+
                             '</tr>';
                            $('#team-list').append(result);
                        }
                    }
                },'json');
            });
            $('.team-salesman-btn').on('click',function() 
            {
                $.post('{$changeSalesmanUrl}',$('#order-team-form').serialize(),function(rs) 
                {
                    var  result = null;
                    var  span_result = null;
                    if(rs.status == 200)
                    {
                        result = 
                         '<tr class="list-item" data-id="'+rs.data.id+'" data-rate="'+rs.data.divide_rate+'">'+
                         '<th class="cost-list-name">'+rs.data.administrator_name+'</th>'+
                         '<th>'+rs.data.department_name+'</th>'+
                         '<th>'+
                         '<input type="text" class="cls_rate" name="rate[]" value="'+rs.data.divide_rate+'">%'+
                         '<input type="hidden" name="team[]" value="'+rs.data.id+'">' +
                         '</th>'+
                         '<th>'+
                         '<span class="btn btn-xs btn-white del-team-btn">删除</span>' +
                         '</th>'+
                         '</tr>';
                         $('.order_team_rate').text(rs.total_rate+'%');
                         $('#team-list').append(result);
                         $('.salesman-err').empty();
                         $('#administrator_id').empty();
                         $('#order-team-form').trigger('reset.yiiActiveForm');
                         $('#divide_rate').val('');
                         $('.divide_rate'+rs.data.order_id).text('('+rs.total_rate+'%)');
                         $('.team-btn'+rs.data.order_id).find('button').attr('data-rate',rs.total_rate);
                         span_result =
                        '<span data-team-id="'+rs.data.id+'">'+rs.data.administrator_name+
                        '('+rs.data.divide_rate+'%)'+
                        '</span><br>';
                        $('.order-list-team'+rs.data.order_id).append(span_result);
                    }
                    else if(rs.status == 400)
                    {
                        $('.salesman-err').text(rs.message);
                    }
                },'json')
            })
            
            $('.save-rate-btn').click(function() 
            {
              $.post('{$changeRateUrl}',$('#rote-form').serialize(),function(rs) 
                {
                    if(rs.status == 200)
                    {
                        window.location.reload();
                    }
                    else if(rs.status == 400)
                    {
                        $('.rate-error-text').text(rs.message);
                    }
                },'json')
            })
            
            //删除
            $('#team-list').on('click','.del-team-btn',function()
            {
                var _this = $(this);
                var id = _this.parents('.list-item').attr('data-id');
                $.post('{$deleteTeamUrl}',{id:id},function(rs)
                {
                    if(rs.status != 200)
                    {
                    }else{
                        $('.order_team_rate').text(rs.rate+'%');
                        $('.divide_rate'+rs.order_id).text('('+rs.rate+'%)');
                        _this.parents('.list-item').remove();
                        $('.order-list-team'+rs.order_id).find('span[data-team-id="'+id+'"]').remove();
                        $('.team-btn'+rs.order_id).find('button').attr('data-rate',rs.rate);
                    }
                },'json')
            })
            
            $('#team-list').on('blur','.cls_rate',function()
            {
                countRate();
            })
            
            //计算比例
            function countRate() 
            {
                var dataRate = 0;
                $("#team-list tr .cls_rate").each(function (index, item) 
                {
                    dataRate += parseFloat($(this).val());
                });
                var total_rate = 100-dataRate;
                var reg = new RegExp("^[0-9]*$"); 
                if(reg.test(total_rate))
                {
                     $('.order_team_rate').html(total_rate+'%');
                }
            }
JS
        ) ?>
    </div>
<?php endif; ?>
<!--修改订单分成业务人员end-->

<!--修改服务人员start-->
<?php if (Yii::$app->user->can('order-action/change-clerk')): ?>
    <div class="modal fade" id="clerk-modal" role="dialog" aria-labelledby="modal-title">
        <?php
        $changeOrderClerkForm = new \backend\models\ChangeOrderClerkForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['order-action/change-clerk'],
            'id' => 'clerk-form',
            'validationUrl' => ['order-action/change-clerk', 'is_validate' => 1],
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">派单给服务人员</h4>
                </div>

                <div class="modal-body input_box clerk-div">
                    <div class="table-responsive change-clerk-table">
                        <table class="footable table table-striped ">
                            <thead>
                            <tr>
                                <th>选择</th>
                                <th>姓名</th>
                                <th>手机号</th>
                                <th>地区</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <?=
                    Html::activeHiddenInput($changeOrderClerkForm, 'order_id', ['id' => 'change-order-clerk-form_order_id']); ?>
                    <span class="text-danger warning-active"><?= Html::error($changeOrderClerkForm, 'clerk_id'); ?><?= Html::error($changeOrderClerkForm, 'order_id'); ?></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary clerk-sure-btn">保存</button>
                </div>
            </div>
        </div>
        <?php
        $ajaxClerkListUrl = \yii\helpers\Url::to(['clerk/ajax-list']);
        $changeClerkTemplate = '<tr><td><input name="clerk_id" type="radio" value="{id}" /></td><td>{name}</td><td>{phone}</td><td>{area}</td></tr>';
        $this->registerJs(<<<JS
        $('.clerk-btn, .clerk-allot-btn').click(function(){
            //清空表格内容
            $('.change-clerk-table table tbody').empty();
            var clerk_type = $(this).attr('data-clerk-type');
            if(clerk_type === 'clerk_allot_type')
            {
                $('#clerk-form').find('.modal-title').text('派单给服务人员');
            }
            else
            {
                $('#clerk-form').find('.modal-title').text('修改服务人员');  
            }
            var changeClerkTemplate = '{$changeClerkTemplate}';
            var id = $(this).attr('data-id');
            var product_id = $(this).attr('data-product-id');
            var district_id = $(this).attr('data-district-id');
            $('#change-order-clerk-form_order_id').val(id);
            $.get('{$ajaxClerkListUrl}', {product_id: product_id,district_id: district_id}, function(rs){
                if(rs.status === 200){
                    for(var i = 0;i < rs['models'].length; i++) {
                    var item = changeClerkTemplate.replace('{id}', rs.models[i].id).replace('{phone}', rs['models'][i]['phone']).replace('{name}', rs['models'][i]['name']).replace('{area}', rs['models'][i]['address']);
                    $('.change-clerk-table table tbody').append(item);
                  }
                }
            },'json');
        });
        
        $('#clerk-form').on('beforeSubmit', function(){
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
<!--修改服务人员end-->

<!--修改价格start-->
<?php if (Yii::$app->user->can('order-action/adjust-price')): ?>
    <div class="modal fade" id="adjust-order-price-modal" role="dialog" aria-labelledby="adjust-order-price-label">
        <?php
        $adjustForm = new \backend\models\AdjustPriceForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['order-action/adjust-price'],
            'id' => 'adjust-price-form',
            'validationUrl' => ['order-action/adjust-price', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-3',
//                    'offset' => 'col-sm-offset-1',
                    'wrapper' => 'col-sm-8',
                    'hint' => 'col-sm-8 col-sm-offset-3',
                ],
            ],
        ]);
        ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="adjust-order-price-label">修改价格</h4>
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
                    <?= $form->field($adjustForm, 'adjust_price_reason')->textarea()->hint('(价格修改申请提交后，将手机短信通知订单负责业务员的部门主管，请耐心等候审核。)') ?>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($adjustForm, 'order_id', ['id' => 'adjust-form_order_id']); ?>
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消修改</button>
                    <button type="submit" class="btn btn-primary sure-btn">提交审核</button>
                </div>
            </div>
        </div>
        <?php
        $ajaxGetAdjustInfoUrl = \yii\helpers\Url::to(['order-action/get-adjust-info']);
        $this->registerJs(<<<JS
        $(function() 
        {
            var form = $('#adjust-price-form');
            var adjust_price_origin_price = 0;
            function changeAdjustPrice()
            {
                var adjustPrice = parseFloat(form.find('#adjust_price').val());
                if(isNaN(adjustPrice)) adjustPrice = 0;
                var price = parseFloat(adjust_price_origin_price) + adjustPrice;
                form.find('.field-price .form-control-static').text(fmoney(price));
            }
            $('.adjust-price-btn').on('click', function()
            {
                form.find('.warning-active').text('');
                changeAdjustPrice();
                var modal = $('#adjust-order-price-modal');
                var id = $(this).attr('data-id');
                form.trigger('reset.yiiActiveForm');
                $('#adjust-form_order_id').val(id);
                adjust_price_origin_price = $(this).attr('data-original-price');
                form.find('.field-origin_price .form-control-static').text(fmoney($(this).attr('data-original-price')));
                $.get('{$ajaxGetAdjustInfoUrl}', {order_id: id}, function(rs)
                {
                    modal.find('.input_box').show();
                    modal.find('.loading').hide();
                    if(rs['status'] === 200)
                    {
                        form.find('#adjust_price').val(rs['data']['adjust_price']);
                        form.find('#adjust_price_reason').val(rs['data']['adjust_price_reason']);
                        changeAdjustPrice();
                    }
                    else
                    {
                        changeAdjustPrice();
                    }
                }, 'json');
            });
            form.find('#adjust_price').change(function()
            {
                changeAdjustPrice();
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
<?php endif; ?>
<!--修改价格end-->

<!--批量修改价格start-->
<?php if (Yii::$app->user->can('virtual-order-action/batch-adjust-price')): ?>
    <div class="modal fade" id="batch-adjust-order-price-modal" role="dialog" aria-labelledby="batch-adjust-order-price-label">
        <?php
        $batchAdjustPriceForm = new \backend\models\BatchAdjustPriceForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['virtual-order-action/change-adjust-price'],
            'id' => 'batch-adjust-price-form',
            'validationUrl' => ['virtual-order-action/change-adjust-price', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-4',
                    'wrapper' => 'col-sm-8',
                    'hint' => 'col-sm-8 col-sm-offset-4',
                ],
            ],
        ]);
        ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="batch-adjust-order-price-label">批量修改订单价格<span class="text-warning">(固定价格变动)</span></h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($batchAdjustPriceForm, 'origin_price')->staticControl() ?>
                    <?= $form->field($batchAdjustPriceForm, 'adjust_price')->textInput() ?>
                    <div class="form-group field-batchadjustpriceform-adjust_price">
                        <label class="control-label col-sm-4"></label>
                        <div class="col-sm-8">（即每个子订单调整的金额；需输入数字，+50为增加50元，-50为减少50元；且变动后，每个子订单金额都会相加。）</div>
                        <div class="help-block col-sm-12 col-sm-offset-3"></div>
                    </div>
                    <?= $form->field($batchAdjustPriceForm, 'price')->staticControl() ?>
                    <?= $form->field($batchAdjustPriceForm, 'adjust_price_reason')->textarea()->hint('(价格修改申请提交后，将手机短信通知订单负责业务员的部门主管，请耐心等候审核。)') ?>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($batchAdjustPriceForm, 'order_id'); ?>
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
            var form = $('#batch-adjust-price-form');
            var batch_adjust_price_origin_price = 0;
            function changeAdjustPrice()
            {
                var adjustPrice = parseFloat(form.find('#batchadjustpriceform-adjust_price').val());
                if(isNaN(adjustPrice)) adjustPrice = 0;
                var qty = checkedQty();
                var totalAdjustPrice = accMul(adjustPrice,qty);
                var price = parseFloat(batch_adjust_price_origin_price) + totalAdjustPrice;
                form.find('.field-batchadjustpriceform-price .form-control-static').text(fmoney(price));
            }
            $('.batch-adjust-price-btn').on('click', function()
            {
                form.find('.warning-active').text('');
                changeAdjustPrice();
                form.trigger('reset.yiiActiveForm');
                var order_ids = checkedValues();
                $('#batchadjustpriceform-order_id').val(order_ids);
                batch_adjust_price_origin_price = $(this).attr('data-original-price');
                console.log(batch_adjust_price_origin_price);
                form.find('.field-batchadjustpriceform-origin_price .form-control-static').text(fmoney($(this).attr('data-original-price')));
                form.find('.field-batchadjustpriceform-price .form-control-static').text(fmoney($(this).attr('data-original-price')));
            });
            form.find('#batchadjustpriceform-adjust_price').change(function()
            {
                changeAdjustPrice();
            });
            form.on('beforeSubmit', function(){
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
        });    
JS
        ) ?>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>
<?php endif; ?>
<!--批量修改价格end-->

<!--批量修改付款方式start-->
<?php if (Yii::$app->user->can('virtual-order-action/payment-mode')): ?>
<div class="modal fade" id="payment-mode-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $paymentMode = new \backend\models\PaymentModeForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['virtual-order-action/change-payment-mode'],
        'id' => 'change-payment-form',
        'validationUrl' => ['virtual-order-action/change-payment-mode', 'is_validate' => 1],
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
                <h4 class="modal-title">批量修改付款方式</h4>
            </div>

            <div class="modal-body input_box clerk-div">
                <?= $form->field($paymentMode, 'is_installment')->dropDownList(['一次付款','分期付款']); ?>
                <?= $form->field($paymentMode, 'content')->textarea(); ?>
            </div>
            <div class="modal-footer">
                <?= Html::activeHiddenInput($paymentMode, 'order_id'); ?>
                <?= Html::activeHiddenInput($paymentMode, 'status'); ?>
                <span class="text-danger warning-active">
                    <?= Html::error($paymentMode, 'is_installment'); ?>
                    <?= Html::error($paymentMode, 'content'); ?>
                    <?= Html::error($paymentMode, 'order_id'); ?>
                </span>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary clerk-sure-btn">确定</button>
            </div>
        </div>
    </div>
    <?php
    $this->registerJs(<<<JS
    $('.payment-btn').click(function()
    {
        $('#payment-mode-modal').find('.modal-title').text('批量修改付款方式');
        var form = $('#change-payment-form');
        form.trigger('reset.yiiActiveForm');
        form.find('.warning-active').text('');
        var order_ids = checkedValues();
        $('#paymentmodeform-order_id').val(order_ids);
        $('#paymentmodeform-status').val(1);
    });

    $('.pending-payment-btn').click(function()
    {
        $('#payment-mode-modal').find('.modal-title').text('批量修改付款方式');
        var form = $('#change-payment-form');
        form.trigger('reset.yiiActiveForm');
        form.find('.warning-active').text('');
        var order_ids = checkedValues();
        $('#paymentmodeform-order_id').val(order_ids);
        $('#paymentmodeform-status').val(0);
    });
    
    $('#change-payment-form').on('beforeSubmit', function()
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
<!--批量修改付款方式end-->

<!--跟进记录start-->
<?php if (Yii::$app->user->can('follow-record/create')): ?>
    <div class="modal fade" id="order-follow-record-modal" role="dialog" aria-labelledby="modal-title">
        <div class="modal-dialog" role="document">
            <?php
            $followRecordForm = new \backend\models\FollowRecordForm();
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['follow-record/create'],
                'validationUrl' => ['follow-record/create', 'is_validate' => 1],
                'enableAjaxValidation' => true,
                'id' => 'order-follow-record-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-3',
                        'offset' => 'col-sm-offset-3',
                        'wrapper' => 'col-sm-7',
                        'hint' => 'col-sm-offset-3 col-sm-8',
                    ],
                ],
            ]); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">跟进记录</h4>
                </div>
                <div class="modal-body input_box">
                    <p id="delete-follow-record-hint">确定删除吗？</p>
                    <table class="table table-bordered table-hover follow-record-list">
                        <thead>
                        <tr>
                            <th>跟进时间</th>
                            <th>跟进状态</th>
                            <th>备注信息</th>
                            <th>跟进人</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="6"><span class="btn btn-default follow-record-add">添加跟进记录</span></td>
                        </tr>
                        </tfoot>
                    </table>
                    <div class="follow-record-add-form">
                        <?= $form->field($followRecordForm, 'is_follow')->checkbox() ?>
                        <?= $form->field($followRecordForm, 'next_follow_time')->widget(DateTimePicker::className(), [
                            'clientOptions' => [
                                'format' => 'yyyy-mm-dd hh:00',
                                'language' => 'zh-CN',
                                'autoclose' => true,
                            ],
                            'clientEvents' => [],
                        ]) ?>
                        <?= $form->field($followRecordForm, 'follow_remark')->textarea(['maxlength' => 80]) ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($followRecordForm, 'virtual_order_id', ['id' => 'follow-record-form_virtual_order_id']); ?>
                    <?= Html::activeHiddenInput($followRecordForm, 'order_id', ['id' => 'follow-record-form_order_id']); ?>
                    <div class="save-btn-follow-record" style="display: none;">
                        <span class="text-danger warning-active"></span>
                        <button type="button" class="btn btn-default cancel-add-follow-record">取消</button>
                        <button type="submit" class="btn btn-primary" id="confirm-follow-record-add">确定</button>
                    </div>
                    <div class="delete-btn-follow-record" style="display: none;">
                        <span class="text-danger warning-active"></span>
                        <button type="button" class="btn btn-default cancel-delete-follow-record">取消</button>
                        <button type="button" class="btn btn-primary" id="confirm-follow-record-delete">确定</button>
                    </div>
                </div>
            </div>
            <?php
            $followRecordTemplate = '<tr><td>{time}</td><td>{follow}</td><td>{remark}</td><td>{creator_name}</td></tr>';
            $ajaxFollowRecordInfoUrl = \yii\helpers\Url::to(['follow-record/ajax-list']);
            $this->registerJs(<<<JS
        var followRecordModal = $('#order-follow-record-modal');
        var followRecordTemplate = '{$followRecordTemplate}';
        $('.see-order-follow-record').click(function(){
            showList();
            followRecordModal.find('table tbody').empty();
            followRecordModal.find('.warning-active').empty();
            var id = $(this).attr('data-id');
            var order_id = $(this).attr('data-order-id');
            var isCancel = $(this).attr('data-is-cancel');
            if(isCancel === '1')
            {
                followRecordModal.find('.follow-record-add').hide();
            }
            else
            {
                followRecordModal.find('.follow-record-add').show();
            }
            $('#follow-record-form_virtual_order_id').val(id);
            $('#follow-record-form_order_id').val(order_id);
            $.get('{$ajaxFollowRecordInfoUrl}', {virtual_order_id:id}, function(rs){
                if(rs.status === 200)
                {
                    var models = rs['models'];
                    for(var i = 0; i < models.length; i++)
                    {
                        var item = followRecordTemplate.replace('{time}', models[i]['created_at'])
                            .replace('{follow}', models[i]['is_follow'] ? '跟进中' : '')
                            .replace('{remark}', models[i]['follow_remark'])
                            .replace('{creator_name}', models[i]['creator_name']);
                        followRecordModal.find('table tbody').append(item);
                        $('#order-follow-record-modal').trigger('reset.yiiActiveForm');
                    }
                }
                else
                {
                    followRecordModal.find('.warning-active').text(rs.message);
                }
            }, 'json');
        });
        $('.follow-record-add').click(function(){
            $('.field-followrecordform-next_follow_time').show();
            $('#order-follow-record-form').trigger('reset.yiiActiveForm');
            showAddDistrictPriceDetail();
        });
        $('.cancel-add-follow-record').click(function(){
            showList();
        });
        $('#followrecordform-is_follow').click(function(){
            if($('#followrecordform-is_follow').is(':checked')){
                $('.field-followrecordform-next_follow_time').show();
            }else{
                $('.field-followrecordform-next_follow_time').hide();
            }
        });
        showList();
        function showList()
        {
            followRecordModal.find('.modal-title').text('跟进记录');
            $('.follow-record-add-form').hide();
            $('.follow-record-list').show();
            $('.save-btn-follow-record').hide();
            $('.delete-btn-follow-record').hide();
            $('#delete-follow-record-hint').hide();
            $('#order-follow-record-modal').find('.warning-active').text('');
        }
        
        function showAddDistrictPriceDetail()
        {
            followRecordModal.find('.modal-title').text('添加跟进记录');
            $('.follow-record-add-form').show();
            $('.save-btn-follow-record').show();
            $('.follow-record-list').hide();
            $('.delete-btn-follow-record').hide();
             $('#delete-follow-record-hint').hide();
            followRecordModal.find('.warning-active').text('');
        }
        $('#order-follow-record-form').on('beforeSubmit', function(){
            var form = $(this);
            $.post(form.attr('action'), form.serialize(), function(rs){
                if(rs.status === 200)
                {
                    form.trigger('reset.yiiActiveForm');
                    var item = followRecordTemplate.replace('{time}', rs['model']['next_follow_time'])
                            .replace('{follow}', rs['model']['is_follow'] ? '跟进中' : '')
                            .replace('{remark}', rs['model']['follow_remark'])
                            .replace('{creator_name}', '{$administrator->name}');
                        followRecordModal.find('table tbody').append(item);
                    showList();
                    if(!rs['model']['is_follow']){
                        window.location.reload();
                    }
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
    </div>
<?php endif; ?>
<!--跟进记录end-->

<!--批量财务明细编号start-->
<?php if (Yii::$app->user->can('virtual-order-action/change-financial')): ?>
<div class="modal fade" id="financial-code-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $orderFinancialModel = new \backend\models\BatchOrderFinancialForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['virtual-order-action/change-financial'],
        'validationUrl' => ['virtual-order-action/change-financial', 'is_validate' => 1],
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">批量编辑财务明细编号</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($orderFinancialModel, 'financial_code')->textInput() ?>
                <div class="col-sm-offset-3 col-sm-8 text-danger"> 注意：请输入字母+数字的组合，例如C1234</div>
            </div>
            <?= \yii\helpers\Html::activeHiddenInput($orderFinancialModel, 'order_id')?>
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
    $(function() 
    {
        $('.financial-code-btn').click(function()
        {
            var form = $('#order-financial-form');
            form.trigger('reset.yiiActiveForm');
            form.find('.warning-active').text('');
            var order_ids = checkedValues();
            $('#batchorderfinancialform-order_id').val(order_ids);
        });
        
        $('#order-financial-form').on('beforeSubmit', function()
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
    });
JS
    );
    ?>
</div>
<?php endif; ?>
<!--批量财务明细编号end-->

<!--订单业绩提点月-->
<?php if (Yii::$app->user->can('virtual-order-action/change-settlement-month')): ?>
<div class="modal fade" id="settlement-month-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <?php
    $batchOrderSettlementMonthForm = new \backend\models\BatchOrderSettlementMonthForm();
    $batchOrderSettlementMonthForm->settlement_month = date('Ym');
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['virtual-order-action/change-settlement-month'],
        'validationUrl' => ['virtual-order-action/change-settlement-month', 'is_validate' => 1],
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
                    <h4 class="modal-title">批量编辑订单业绩提点月</h4>
                </div>
                <div class="modal-body">
                    <?= $form->field($batchOrderSettlementMonthForm, 'settlement_month')->textInput()->hint('例：201804') ?>
                    <?= Html::activeHiddenInput($batchOrderSettlementMonthForm, 'order_id') ?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary expected-total-cost-btn">保存</button>
                </div>
            </div>
        </div>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
    <?php
    $this->registerJs(<<<JS
    $(function() 
    {
        $('.settlement-month-btn').click(function()
        {
            var form = $('#settlement-month-form');
            form.trigger('reset.yiiActiveForm');
            form.find('.warning-active').text('');
            var order_ids = checkedValues();
            $('#batchordersettlementmonthform-order_id').val(order_ids);
        });
        
        $('#settlement-month-form').on('beforeSubmit', function()
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
    });
JS
    );
    ?>
</div>
<?php endif; ?>
<!--订单业绩提点月-->

<?php
$this->registerJs(<<<JS
$(function() 
{
    $('.update-pay-mode').click(function() 
    {
        var order_id = $(this).attr('data-id');
        var is_installment = $(this).attr('data-is_installment');
        $('#paymentmodeform-order_id').val(order_id);
        $('#paymentmodeform-is_installment').val(is_installment).attr('selected','true');
        $('#payment-mode-modal').find('.modal-title').text('修改付款方式');
        $('#paymentmodeform-status').val(1);
    })
});

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
function accMul(arg1,arg2)     
{     
    var m=0,s1=arg1.toString(),s2=arg2.toString();  
      
    try{m+=s1.split(".")[1].length}catch(e){}     
    try{m+=s2.split(".")[1].length}catch(e){}     
    return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m)     
};
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
};
JS
)
?>