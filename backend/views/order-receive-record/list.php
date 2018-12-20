<?php
use backend\models\ConfirmPayForm;
use backend\models\OrderSearch;
use backend\widgets\LinkPager;
use common\models\BusinessSubject;
use common\models\MonthProfitRecord;
use common\models\Order;
use common\models\PayRecord;
use common\models\Property;
use common\utils\Decimal;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use zhuravljov\yii\widgets\DateTimePicker;

/** @var \yii\web\View $this */
/** @var \yii\data\DataProviderInterface $dataProvider */
/** @var \common\models\Order[] $models */
/** @var OrderSearch $searchModel */
/** @var string $status */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$actionUniqueId = Yii::$app->controller->action->uniqueId;
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
$this->title = '订单认领记录';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-lg-12">
        <div class="tabs-container">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel-body" style="padding: 25px 20px 10px;border-bottom: 3px solid #e7eaec;">
                        <div class="page-select2-area">
                            <?php
                            $categoryUrl = \yii\helpers\Url::to(['product-category/ajax-list', 'parent_id' => '__parent_id__']);
                            $productUrl = \yii\helpers\Url::to(['product/ajax-list', 'category_id' => '__category_id__']);

                            $cityUrl = \yii\helpers\Url::to(['region/ajax-cities', 'province_id' => '__province_id__']);
                            $districtUrl = \yii\helpers\Url::to(['region/ajax-districts', 'city_id' => '__city_id__']);
                            $labelOptions = ['labelOptions' => ['class' => false]];
                            $form = ActiveForm::begin(['layout' => 'inline', 'method' => 'get', 'action' => ['order-receive-record/' . Yii::$app->controller->action->id]]); ?>
                            <div>
                            	<!--商品类目-->
                            	<div class="select2-options">
	                                <?= $form->field($searchModel, 'top_category_id', $labelOptions)->widget(Select2Widget::className(), [
	                                    'serverUrl' => \yii\helpers\Url::to(['product-category/ajax-list']),
	                                    'itemsName' => 'categories',
	                                    'selectedItem' => $searchModel->topCategory ? [$searchModel->topCategory->id => $searchModel->topCategory->name] : [],
	                                    'options' => ['prompt' => '选择类目', 'class' => 'form-control'],
	                                    'placeholderId' => '0',
	                                    'placeholder' => '选择类目',
	                                    'width'=>'118',
	                                    'eventSelect' => new JsExpression("
	                                        $('#category_id').val('0').trigger('change');
	                                    ")
	                                ]) ?>
	                                <?= $form->field($searchModel, 'category_id', $labelOptions)->widget(Select2Widget::className(), [
	                                    'serverUrl' => \yii\helpers\Url::to(['product-category/ajax-list']),
	                                    'itemsName' => 'categories',
	                                    'selectedItem' => $searchModel->category ? [$searchModel->category->id => $searchModel->category->name] : [],
	                                    'options' => ['prompt' => '选择类目', 'class' => 'form-control'],
	                                    'placeholderId' => '0',
	                                    'placeholder' => '选择类目',
	                                    'width'=>'118',
	                                    'eventOpening' => new JsExpression("
	                                        var id = $('#top_category_id').val();
	                                        serverUrl = '{$categoryUrl}'.replace('__parent_id__', id ? id : '-1');
	                                    ")
	                                ]) ?>
	                                <?= $form->field($searchModel, 'product_id', $labelOptions)->widget(Select2Widget::className(), [
	                                    'serverUrl' => \yii\helpers\Url::to(['product/ajax-list', 'category_id' => '__category_id__']),
	                                    'itemsName' => 'products',
	                                    'selectedItem' => $searchModel->product ? [$searchModel->product->id => $searchModel->product->name] : [],
	                                    'options' => ['prompt' => '选择类目', 'class' => 'form-control'],
	                                    'placeholderId' => '0',
	                                    'placeholder' => '选择类目',
	                                    'width'=>'118',
	                                    'eventOpening' => new JsExpression("
	                                        var id = $('#category_id').val();
	                                        serverUrl = '{$productUrl}'.replace('__category_id__', id ? id : '-1');
	                                    ")
	                                ]) ?>
								</div>
								
								<!--地区-->
                                <div class="select2-options">
	                                <?= $form->field($searchModel, 'province_id', $labelOptions)->widget(Select2Widget::className(), [
	                                    'serverUrl' => \yii\helpers\Url::to(['region/ajax-provinces']),
	                                    'itemsName' => 'provinces',
	                                    'selectedItem' => $searchModel->province ? [$searchModel->province->id => $searchModel->province->name] : [],
	                                    'options' => ['class' => 'form-control', 'prompt' => '请选择省份'],
	                                    'placeholderId' => '0',
	                                    'placeholder' => '请选择省份',
	                                    'width'=>'118',
	                                    'eventSelect' => new JsExpression("
	                                        $('#city_id').val('0').trigger('change');
	                                        $('#district_id').val('0').trigger('change');
	                                    ")
	                                ]); ?>
	                                <?= $form->field($searchModel, 'city_id', $labelOptions)->widget(Select2Widget::className(), [
	                                    'serverUrl' => \yii\helpers\Url::to(['region/ajax-cities', 'province_id' => '__province_id__']),
	                                    'itemsName' => 'cities',
	                                    'selectedItem' => $searchModel->city ? [$searchModel->city->id => $searchModel->city->name] : [],
	                                    'options' => ['class' => 'form-control', 'prompt' => '请选择城市'],
	                                    'placeholderId' => '0',
	                                    'placeholder' => '请选择城市',
	                                    'width'=>'118',
	                                    'eventSelect' => new JsExpression("
	                                        $('#district_id').val('0').trigger('change');
	                                    "),
	                                    'eventOpening' => new JsExpression("
	                                        var id = $('#province_id').val();
	                                        serverUrl = '{$cityUrl}'.replace('__province_id__', id ? id : '-1');
	                                    ")
	                                ]); ?>
	                                <?= $form->field($searchModel, 'district_id', $labelOptions)->widget(Select2Widget::className(), [
	                                    'serverUrl' => \yii\helpers\Url::to(['region/ajax-districts', 'city_id' => '__city_id__']),
	                                    'selectedItem' => $searchModel->district ? [$searchModel->district->id => $searchModel->district->name] : [],
	                                    'options' => ['class' => 'form-control', 'prompt' => '请选择地区'],
	                                    'placeholderId' => '0',
	                                    'placeholder' => '请选择地区',
	                                    'itemsName' => 'districts',
	                                    'width'=>'118',
	                                    'eventOpening' => new JsExpression("
	                                        var id = $('#city_id').val();
	                                        serverUrl = '{$districtUrl}'.replace('__city_id__', id ? id : '-1');
	                                    ")
	                                ]); ?>
                                </div>
                                
                                <!--订单来源-->
                                <div class="select2-options">
	                                <?= $form->field($searchModel, 'source_app', $labelOptions)->widget(Select2Widget::className(), [
	                                    'selectedItem' => OrderSearch::getSourceApps(),
	                                    'placeholderId' => '0',
	                                    'placeholder' => '请选择来源',
	                                    'options' => ['class' => 'form-control', 'prompt' => '请选择来源'],
	                                    'static' => true,
	                                ]) ?>
	                                <?= $form->field($searchModel, 'is_proxy')->widget(Select2Widget::className(), [
	                                    'selectedItem' => ['2' => '后台下单', '1' => '客户自主下单'],
	                                    'placeholderId' => '0',
	                                    'placeholder' => '下单方式',
	                                    'options' => ['class' => 'form-control', 'prompt' => '下单方式'],
	                                    'static' => true,
	                                ]) ?>
                                </div>

	                            <!--下单时间-->
	                            <div  class="select2-options">
	                                <b>下单时间</b>
	                                <?= $form->field($searchModel, 'starting_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
	                                    'options' => ['class' => 'form-control', 'style'=>'width:146px;margin-left:6px;'],
	                                ]) ?>
	                                <?= $form->field($searchModel, 'end_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
	                                    'options' => ['class' => 'form-control', 'style'=>'width:146px;margin-left:6px'],
	                                ]) ?>
	                            </div>
	                            
	                            <!--首次付款时间-->
	                            <div  class="select2-options">
	                                <b>首次付款时间</b>
	                                <?= $form->field($searchModel, 'first_pay_start_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
	                                    'options' => ['class' => 'form-control', 'style'=>'width:146px;margin-left:6px;'],
	                                ]) ?>
	                                <?= $form->field($searchModel, 'first_pay_end_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
	                                    'options' => ['class' => 'form-control', 'style'=>'width:146px;margin-left:6px;'],
	                                ]) ?>
	                            </div>
	                            
	                            <div class="select2-options" >
	                                <?= $form->field($searchModel, 'type', $labelOptions)->widget(Select2Widget::className(), [
	                                    'selectedItem' => OrderSearch::getTypes(),
	                                    'placeholderId' => '0',
	                                    'placeholder' => '请选择类型',
	                                    'options' => ['class' => 'form-control', 'prompt' => '请选择类型'],
	                                    'static' => true,
	                                ]) ?>
	                                <?= $form->field($searchModel, 'keyword')->textInput() ?>
	                                
	                                <?= $form->field($searchModel, 'status')->hiddenInput(['value'=>Yii::$app->requestedAction->id]) ?>
	                                <button type="submit" class="btn btn-sm btn-primary m-t-n-xs">搜索</button>
	                            </div>
                            </div>
                            <?php \yii\bootstrap\ActiveForm::end(); ?>
                        </div>
                    </div>
                    <div class="panel-body" style="padding: 0;margin-bottom: 36px;">
                            <div class="table-responsive top-pagination" style="height:49px;padding: 9px 20px;border-bottom: 1px solid #e7eaec;">
                                <div class="row" style="margin: 0;">
                                    <div class="col-lg-12" style="padding: 0;">
                                        订单状态：
                                        <a href="<?= Url::to(['pending-payment']) ?>" <?php if($actionUniqueId == 'order-receive-record/pending-payment'): ?>class="btn btn-xs btn-primary"<?php endif; ?> style="margin-left: 10px">待付款</a>
                                        <a href="<?= Url::to(['unpaid']) ?>" <?php if($actionUniqueId == 'order-receive-record/unpaid'): ?>class="btn btn-xs btn-primary"<?php endif; ?> style="margin-left: 10px">未付清</a>
                                        <a href="<?= Url::to(['pending-assign']) ?>" <?php if($actionUniqueId == 'order-receive-record/pending-assign'): ?>class="btn btn-xs btn-primary"<?php endif; ?> style="margin-left: 10px">待分配</a>
                                        <a href="<?= Url::to(['refund']) ?>" <?php if($actionUniqueId == 'order-receive-record/refund'): ?>class="btn btn-xs btn-primary"<?php endif; ?> style="margin-left: 10px">退款中</a>
                                        <a href="<?= Url::to(['pending-service']) ?>" <?php if($actionUniqueId == 'order-receive-record/pending-service'): ?>class="btn btn-xs btn-primary"<?php endif; ?> style="margin-left: 10px">待服务</a>
                                        <a href="<?= Url::to(['in-service']) ?>" <?php if($actionUniqueId == 'order-receive-record/in-service'): ?>class="btn btn-xs btn-primary"<?php endif; ?> style="margin-left: 10px">服务中</a>
                                        <a href="<?= Url::to(['completed']) ?>" <?php if($actionUniqueId == 'order-receive-record/completed'): ?>class="btn btn-xs btn-primary"<?php endif; ?> style="margin-left: 10px">服务完成</a>
                                        <a href="<?= Url::to(['break']) ?>" <?php if($actionUniqueId == 'order-receive-record/break'): ?>class="btn btn-xs btn-primary"<?php endif; ?> style="margin-left: 10px">服务终止</a>
                                        <a href="<?= Url::to(['all']) ?>" <?php if($actionUniqueId == 'order-receive-record/all'): ?>class="btn btn-xs btn-primary"<?php endif; ?> style="margin-left: 10px">全部</a>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive top-pagination" style="height:49px;padding: 9px 20px;border-bottom: 1px solid #e7eaec;">
                    		<div class="row" style="margin: 0;">
                    			<div class="col-lg-12" style="padding: 0;">
                    				<?=
		                            LinkPager::widget([
		                                'pagination' => $pagination
		                            ]);
		                            ?>
                    			</div>
                    		</div>
                    		</div>
                        <div class="table-responsive" style="padding: 20px 20px 0;">
                            
                            <table class="table table-bordered" style="border: none;margin: 0;">
                                <thead>
                                <tr style="border-top: 1px solid #e7eaec;">
                                    <th style="width: 163px;">订单信息</th>
                                    <th style="width: 136px;">客户信息</th>
                                    <th style="width: 203px;">商品信息</th>
                                    <th style="width: 164px;">业务人员</th>
                                    <th style="width: 91px;">客服人员</th>
                                    <th style="width: 91px;">服务人员</th>
                                    <th style="width: 86px;">付款方式</th>
                                    <th class="text-right" style="width: 186px;">支付信息</th>
                                    <th class="text-center" style="width: 126px;">订单状态</th>
                                    <th class="text-center" style="width: 91px;">凭证</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($models as $order):
                                    $virtualModel = $order->virtualOrder;
                                    ?>
                                        <tr>
                                            <!-- 订单信息 -->
                                            <td style="vertical-align: middle;">
                                                <p class="text-muted"><?= $virtualModel->sn; ?></p>
                                                <p class="text-muted"><?= Yii::$app->formatter->asDatetime($virtualModel->created_at) ?></p>
                                                <p>
                                                    <?php if ($order->hasDetail()): ?>
                                                        <a href="<?= Url::to(['order/info', 'id' => $order->id]) ?>" target="_blank"><?= $order->sn; ?></a>
                                                    <?php else: ?>
                                                        <?= $order->sn; ?>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="text-muted"><?= $order->getSourceAppName();?></p>
                                                <p class="text-muted"><?= $order->is_proxy ? $order->creator_name.'后台新增' : '客户自主下单'; ?></p>
                                            </td>

                                            <!-- 客户信息 -->
                                            <td style="vertical-align: middle;">
                                                <p><?= $order->user->name; ?></p>
                                                <p><?= $order->user->phone; ?></p>
                                            </td>

                                            <!-- 商品信息 -->
                                            <td style="vertical-align: middle;">
                                                <p><strong><?= $order->product_name; ?></strong></p>
                                                <?php if ($order->district_id): ?>
                                                    <p class="text-muted"><?= $order->province_name; ?>
                                                        -<?= $order->city_name; ?>-<?= $order->district_name; ?></p>
                                                <?php else: ?>
                                                    <p class="text-muted"><?= $order->service_area; ?></p>
                                                <?php endif; ?>
                                                <p>
                                                    <?php if($order->businessSubject): ?>
                                                    <?php if ($order->businessSubject->subject_type == BusinessSubject::SUBJECT_TYPE_DISABLED):?>
                                                        <?= $order->businessSubject->company_name; ?>
                                                    <?php else:?>
                                                        <?= $order->businessSubject->region; ?>
                                                    <?php endif;?>
                                                    <?php endif; ?>
                                                </p>
                                            </td>

                                            <!-- 销售人员 -->
                                            <td  style="vertical-align: middle;">
                                                负责人：<br>
                                                <?= $order->salesman_name; ?>
                                                <?php if($order->salesman_name): ?>
                                                    <span class="divide_rate<?= $order->id ?>"><?= '('.$order->getDivideRate().'%'.')'; ?></span>
                                                <?php endif; ?>
                                                <br>
                                                <?php if($order->salesman_name && !$order->is_vest): ?>
                                                    <br>共享人：<br>
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
                                            <td style="vertical-align: middle; text-align: center;">
                                                <?php if ($order->is_installment): ?>
                                                    <p><strong>分期付款</strong></p>
                                                <?php else: ?>
                                                    <p><strong>一次付款</strong></p>
                                                <?php endif; ?>

                                            </td>

                                            <!-- 支付信息 -->
                                            <td style="vertical-align: middle;">
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
                                                        已付金额：<?= $virtualModel->payment_amount; ?></p>
                                                    <p <?php if ($virtualModel->getPendingPayAmount()): ?>class="text-danger"<?php else:?>class="text-primary"<?php endif; ?>>
                                                        未付金额：<?= $virtualModel->getPendingPayAmount(); ?></p>
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
                                                   <a class="img-cls" data-target="#order-follow-record-modal"
                                                              data-toggle="modal"
                                                              data-url="<?= $order->orderVoucher->getImageUrl(0,0,0) ?>">凭证</a>
                                                </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="11" style="padding: 0;border: none;">
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

<style>
	.receipt-data tr td img.range{
		position: absolute;
		left: 50%;
		z-index: 333;
		max-width: 800px;
		transform: translateX(-50%);
		top: 0;
	}
	.receipt-data tr td .pull-left{
		position: relative;
		width: 90px;
		height: 90px;
	}
</style>

<!--跟进记录start-->
    <!-- <div class="modal fade" id="order-follow-record-modal" role="dialog" aria-labelledby="modal-title"> -->
        <!-- <div class="modal-dialog" role="document"> -->
            <!-- <div class="modal-content"> -->
                <!-- <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div> -->
                <div class="modal input_box text-center fade"   id="order-follow-record-modal" role="dialog" aria-labelledby="modal-title">
                    <img src="" id="big-img">
                </div>
            <!-- </div> -->
        <!-- </div> -->
    <!-- </div> -->
<!--跟进记录end-->
<?php
$this->registerJs(<<<JS

$(".img-cls").on("click",function(){
   var src = $(this).attr('data-url');
   $('#big-img').attr('src', src);
});
$('#big-img').on("click",function(){
   $('#order-follow-record-modal').modal('hide');
});

JS
);
?>