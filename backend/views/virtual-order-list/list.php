<?php
use backend\models\OrderSearch;
use backend\widgets\LinkPager;
use common\models\PayRecord;
use common\utils\Decimal;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use zhuravljov\yii\widgets\DateTimePicker;

/** @var \yii\web\View $this */
/** @var \yii\data\DataProviderInterface $dataProvider */
/** @var \common\models\VirtualOrder[] $models */
/** @var OrderSearch $searchModel */
/** @var string $status */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$actionUniqueId = Yii::$app->controller->action->uniqueId;
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
$this->title = '我的订单';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-lg-12">
        <div class="tabs-container">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel-body" style="padding:0;border-bottom: 3px solid #e7eaec;">
                        <div class="page-select2-area virtual-list-search">
                            <?php
                            $labelOptions = ['labelOptions' => ['class' => false]];
                            $form = ActiveForm::begin(['layout' => 'inline', 'method' => 'get', 'action' => ['virtual-order-list/' . Yii::$app->controller->action->id]]); ?>
                            <div>
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
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete', 'style'=>'width:146px;margin-left:6px;'],
                                    ]) ?>
	                                <?= $form->field($searchModel, 'end_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
	                                    'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete', 'style'=>'width:146px;margin-left:6px'],
	                                ]) ?>
	                            </div>
	                            
                                <!--订单来源-->
                                <div class="select2-options order-source">
	                                <?= $form->field($searchModel, 'source_app', $labelOptions)->widget(Select2Widget::className(), [
	                                    'selectedItem' => OrderSearch::getSourceApps(),
	                                    'placeholderId' => '0',
	                                    'placeholder' => '请选择来源',
	                                    'options' => ['class' => 'form-control', 'prompt' => '请选择来源', 'style'=>'width:128px'],
	                                    'static' => true,
	                                ]) ?>
	                                <?= $form->field($searchModel, 'is_proxy')->widget(Select2Widget::className(), [
	                                    'selectedItem' => ['2' => '后台下单', '1' => '客户自主下单'],
	                                    'placeholderId' => '0',
	                                    'placeholder' => '下单方式',
	                                    'options' => ['class' => 'form-control', 'prompt' => '下单方式', 'style'=>'width:144px'],
	                                    'static' => true,
	                                ]) ?>
                                </div>
                                
	                            <!--首次付款时间-->
	                            <div  class="select2-options first-pay-time">
	                                <b>首次付款时间</b>
	                                <?= $form->field($searchModel, 'first_pay_start_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
	                                    'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete', 'style'=>'width:146px;margin-left:6px;'],
	                                ]) ?>
	                                <?= $form->field($searchModel, 'first_pay_end_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
	                                    'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete', 'style'=>'width:146px;margin-left:6px;'],
	                                ]) ?>
	                            </div>
	                            
	                            <div class="select2-options" >
	                                <?= $form->field($searchModel, 'type', $labelOptions)->widget(Select2Widget::className(), [
	                                    'selectedItem' => \backend\models\VirtualOrderSearch::getTypes(),
	                                    'placeholderId' => '0',
	                                    'placeholder' => '请选择类型',
	                                    'options' => ['class' => 'form-control', 'prompt' => '请选择类型', 'style'=>'width:138px;'],
	                                    'static' => true,
	                                ]) ?>
	                                <?= $form->field($searchModel, 'keyword')->textInput() ?>
	                                
	                                <?= $form->field($searchModel, 'status')->hiddenInput(['value'=>Yii::$app->requestedAction->id]) ?>
	                                <button type="submit" class="btn btn-sm btn-primary m-t-n-xs">搜索</button> 
                                      <div class="advanced-tag-reset" style="display: inline; float : none;">
                                        <a href="<?= Url::to([$actionUniqueId]);?>">重置</a>
                                    </div>
	                            </div>
                            </div>

                            <?php \yii\bootstrap\ActiveForm::end(); ?>
                        </div>
                        <div class="table-responsive top-pagination" style="height:49px;padding: 9px 20px;border-bottom: 1px solid #e7eaec;">
                            <div class="row" style="margin: 0;">
                                <div class="col-lg-12" style="padding: 0;">
                                    订单状态：
                                    <a href="<?= Url::to(['pending-payment']) ?>" <?php if($actionUniqueId == 'virtual-order-list/pending-payment'): ?>class="btn btn-primary btn-sm"<?php endif; ?> style="margin-left: 10px">待付款</a>
                                    <a href="<?= Url::to(['unpaid']) ?>" <?php if($actionUniqueId == 'virtual-order-list/unpaid'): ?>class="btn btn-primary btn-sm"<?php endif; ?> style="margin-left: 10px">未付清</a>
                                    <a href="<?= Url::to(['already-payment']) ?>" <?php if($actionUniqueId == 'virtual-order-list/already-payment'): ?>class="btn btn-primary btn-sm"<?php endif; ?> style="margin-left: 10px">已付款</a>
                                    <a href="<?= Url::to(['cancel']) ?>" <?php if($actionUniqueId == 'virtual-order-list/cancel'): ?>class="btn btn-primary btn-sm"<?php endif; ?> style="margin-left: 10px">已取消</a>
                                    <a href="<?= Url::to(['all']) ?>" <?php if($actionUniqueId == 'virtual-order-list/all'): ?>class="btn btn-primary btn-sm"<?php endif; ?> style="margin-left: 10px">全部</a>
                                </div>
                            </div>
                        </div> 
                        <div class="virtual-list-search-btn">
                        	<a href="javascript:;"><span>更多搜索项</span><i class="glyphicon glyphicon-chevron-down"></i></a>
                        </div>
                    </div>
                    <div class="panel-body" style="padding: 0;margin-bottom: 36px;">
                        <div class="table-responsive top-pagination" style="height:49px;padding: 9px 20px;border-bottom: 1px solid #e7eaec;">
                    		<div class="row" style="margin: 0;">
                                <div class="col-lg-3" style="height:30px;padding: 0;">
                                    <?php if (Yii::$app->user->can('order/create')): ?>
                                        <a href="<?= Url::to(['valet-order/create','status' => 2]) ?>" target="_blank" style="margin-left: 10px;" class="btn btn-sm btn-primary">创建订单</a>
                                    <?php endif; ?>
                                </div>
                    			<div class="col-lg-9" style="padding: 0;">
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
                                    <th class="text-center" style="width: 163px;">订单信息</th>
                                    <th class="text-center" style="width: 136px;">客户信息</th>
                                    <th class="text-center" style="width: 186px;">支付信息</th>
                                    <th class="text-center" style="width: 126px;">交易状态</th>
                                    <th class="text-center" style="width: 91px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($models as $virtualModel): ?>
                                        <tr>
                                            </td>
                                            <!-- 订单信息 -->
                                            <td class="text-center" style="vertical-align: middle;">
                                                <p class="text-muted"><?= Yii::$app->formatter->asDatetime($virtualModel->created_at) ?></p>
                                                <p>订单号：<a href="<?= Url::to(['virtual-order/order', 'vid' => $virtualModel->id]) ?>" target="_blank"><?= $virtualModel->sn; ?></a></p>
                                                <p class="text-muted">服务开始时间：<?= $virtualModel->getFirstBeginService() ? $virtualModel->getFirstBeginService() : '--';?></p>
                                                <p class="text-muted"><?= $virtualModel->order->getSourceAppName();?></p>
                                                <p class="text-muted"><?= $virtualModel->order->is_proxy ? $virtualModel->order->creator_name.'后台新增' : '客户自主下单'; ?></p>
                                            </td>

                                            <!-- 客户信息 -->
                                            <td class="text-center" style="vertical-align: middle;">
                                                <p><?= $virtualModel->user->name; ?></p>
                                                <p><?= $virtualModel->user->phone; ?></p>
                                            </td>

                                            <!-- 支付信息 -->
                                            <td  style="vertical-align: middle;">
                                                <p>商品金额：<?= Decimal::formatCurrentYuan($virtualModel->total_original_amount, 2) ?></p>
                                                <?php if($virtualModel->package_id > 0): ?>
                                                    <p>套餐优惠：<?= Decimal::formatCurrentYuan(-$virtualModel->package_remit_amount, 2, [], [], true) ?></p>
                                                <?php endif; ?>
                                                <?php if($virtualModel->wx_remit_amount > 0): ?>
                                                    <p>微信下单优惠：<?= Decimal::formatCurrentYuan(-$virtualModel->wx_remit_amount, 2, [], [], true) ?></p>
                                                <?php endif; ?>
                                                <?php if(abs($virtualModel->adjust_amount) > 0): ?>
                                                    <p>变动金额：<?= Decimal::formatCurrentYuan($virtualModel->adjust_amount, 2, [], [], true) ?></p>
                                                <?php endif; ?>
                                                <?php if($virtualModel->coupon_remit_amount > 0): ?>
                                                    <p>优惠券金额：<?= Decimal::formatCurrentYuan($virtualModel->coupon_remit_amount, 2) ?></p>
                                                <?php endif; ?>
                                                <?php if(abs($virtualModel->adjust_amount) <= 0 && $virtualModel->wx_remit_amount <= 0 && $virtualModel->package_remit_amount <= 0 && $virtualModel->coupon_remit_amount <= 0): ?>
                                                    <p>优惠金额：<?= Decimal::formatCurrentYuan('0.00', 2) ?></p>
                                                <?php endif; ?>
                                                <p>应付金额：<?= Decimal::formatCurrentYuan($virtualModel->total_amount); ?></p>
                                                <p>已付金额：<?= Decimal::formatCurrentYuan($virtualModel->payment_amount); ?></p>
                                                <p <?php if ($virtualModel->getPendingPayAmount()): ?>class="text-danger"<?php else:?>class="text-primary"<?php endif; ?>>
                                                    未付金额：<?= Decimal::formatCurrentYuan($virtualModel->getPendingPayAmount()); ?>
                                                </p>
                                                <p>可分配回款金额：<?= Decimal::formatCurrentYuan($virtualModel->getTotalAmount()); ?></p>
                                                <?php if ($virtualModel->total_tax > 0): ?>
                                                    <p class="text-muted">
                                                        <small>(含税<?= Decimal::formatCurrentYuan($virtualModel->total_tax, 2) ?>)</small>
                                                    </p>
                                                <?php endif; ?>
                                                <p>申请中的金额：<?= Decimal::formatCurrentYuan($virtualModel->getAdjustTotalPrice()); ?></p>
                                            </td>

                                            <!--以下是订单状态-->
                                            <td class="status" style="vertical-align: middle; text-align: center;">
                                                <?= $virtualModel->getPayStatus(); ?>
                                            </td>

                                            <!--以下是操作部分-->
                                            <td class="text-right" style="vertical-align: middle; text-align: center;">
                                                <?php if ($virtualModel->isPendingPayment() || $virtualModel->isUnpaid() && !$virtualModel->hasRefund()): ?>
                                                <?php if (Yii::$app->user->can('virtual-order-action/receipt') && $administrator->isCompany()): ?>
                                                    <span class="btn btn-xs btn-primary receipt-btn m-t-xs"
                                                          data-target="#receipt-modal"
                                                          data-toggle="modal"
                                                          data-id="<?= $virtualModel->id ?>"
                                                          data-financial-code="<?= $virtualModel->contract ? $virtualModel->contract->serial_number : '--'; ?>"
                                                          data-company-id="<?= $administrator->company_id ?>"
                                                          data-total="<?= $virtualModel->total_amount; ?>"
                                                          data-paid="<?= $virtualModel->payment_amount; ?>"
                                                          data-need="<?= $virtualModel->getPendingPayAmount(); ?>">
                                                        <?= $virtualModel->getReceiptStatusName()?>
                                                            </span>
                                                <?php endif; ?>
                                                <?php endif; ?>
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
<?php $this->registerJs(<<<JS
	$(function(){
		var btnOff = false;
		$('.virtual-list-search-btn a').click(function(){
			if(btnOff){
				$(this).find('span').html('更多搜索项');
				$(this).find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
				$('.order-source').removeClass('virtual-list-search-active');
				$('.first-pay-time').removeClass('virtual-list-search-active');
				btnOff = false;
			}else{
				$(this).find('span').html('全部收起');
				$(this).find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
				$('.order-source').addClass('virtual-list-search-active');
				$('.first-pay-time').addClass('virtual-list-search-active');
				btnOff = true;
			}
			$(this).blur();
		})
	})
JS
); ?>	
	
<!--新建回款弹框start-->
<?php if (Yii::$app->user->can('virtual-order-action/receipt')): ?>
    <div class="modal fade new-receipt" id="receipt-modal" role="dialog" aria-labelledby="modal-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php
                $receiptModel = new \common\models\Receipt();
                $receiptModel->receipt_date = date('Y-m-d');
                $receiptModel->is_separate_money = 1;
                $receiptModel->is_send_sms = 1;
                $form = \yii\bootstrap\ActiveForm::begin([
                    'action' => ['receipt/create'],
                    'id' => 'receipt-order-form',
                    'validationUrl' => ['receipt/validation', 'is_validate' => 1],
                    'enableAjaxValidation' => true,
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-3',
                            'offset' => '',
                            'wrapper' => 'col-sm-8',
                        ],
                    ],
                ]);
                ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">新建回款<span class="text-danger">（虚拟订单-合同）</span></h4>
                </div>

                <div class="modal-body input_box">
                    <div class="receipt-modal-top">
                    	<div class="receipt-header clearfloat">
                    		<div class="col-sm-3 clearfloat">
                                <div class="receipt-header-name">业务主体:</div>
                                <div class="receipt-header-text" id="bussnessName"></div>
                    		</div>
                    		<div class="col-sm-3">
                                <div class="receipt-header-name">下单方式:</div>
                                <div class="receipt-header-text" id="orderWay"></div>
                    		</div>
                    		<div class="col-sm-3">
                                <div class="receipt-header-name">申请回款时间:</div>
                                <div class="receipt-header-text"><?= date('Y-m-d H:i:s',time()); ?></div>
                    		</div>
                    		<div class="col-sm-3 text-right">
                                <div class="receipt-header-name">财务明细编号:</div>
                                <div class="receipt-header-text" id="financialNumber"></div>
                    		</div>
                    	</div>
                    	<div class="receipt-list">
                    		<div class="receipt-list-item clearfloat">
                    			<div class="receipt-item-left">
                    				<span>虚拟订单号：</span>
                    				<p id="virtualNumber"></p>
                    			</div>
                    			<div class="receipt-item-right">
                    				<span>订单金额明细：</span>
                    				<p id="receipt-money-info"></p>
                    			</div>
                    		</div>
                    		<div class="receipt-list-item clearfloat">
                    			<div class="col-sm-4">
                    				<?= $form->field($receiptModel, 'payment_amount')->textInput() ?>
                    			</div>
                    			<div class="col-sm-4">
	                    			<?= $form->field($receiptModel, 'receipt_date')->widget(DateTimePicker::className(), [
			                            'clientOptions' => [
			                                'format' => 'yyyy-mm-dd',
			                                'language' => 'zh-CN',
			                                'autoclose' => true,
			                                'minView' => 'month',
			                            ],
			                        ]) ?>
		                        </div>
		                        <div class="col-sm-4">
		                        	<?= $form->field($receiptModel, 'receipt_company')->textInput() ?>
		                        </div>
                    		</div>
                    		<div class="receipt-list-item clearfloat">
                    			<div class="col-sm-4">
                    				<?= $form->field($receiptModel, 'pay_method')->dropDownList(\yii\helpers\ArrayHelper::merge(['' => '请选择回款方式'], PayRecord::getPayMethod())) ?>
                        		</div>
                        		<div class="col-sm-4">
                        			<?= $form->field($receiptModel, 'pay_account')->textInput() ?>
                        		</div>
                        		<div class="col-sm-4">
                        			<?= $form->field($receiptModel, 'invoice')->dropDownList([])->label('是否开票'); ?>
                                       
                    			</div>
                    		</div>
                    		<div class="receipt-list-item" style="padding-bottom: 0;">
                    			<div class="receipt-item-upload">
	                    			<?php $field = $form->field($receiptModel, 'pay_images')->hiddenInput(['id' => 'pay_images']);
			                        $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
			                                'buttonTitle' => '上传',
			                                'name' => 'file',
			                                'serverUrl' => ['receipt/upload'],
			                                'formData' =>[
			                                    Yii::$app->request->csrfParam => Yii::$app->request->csrfToken,
			                                ],
			                                'done' => new \yii\web\JsExpression(<<<JS
				                                function(e, data) {
				                                    $.each(data.result.files, function (index, file) {
				                                        if(file.error)
				                                        {
				                                            $(".field-pay_images .help-block").html(file.error);
				                                        }
				                                        else
				                                        {
				                                            var delBtn = '<span class="delete-receipt-image btn btn-xs btn-danger" data-key="'+file["key"]+'">删除</span>';
				                                            var input = $("#pay_images");
				                                            $("#pay_images-list").append($("<div class=\"thumbnail pull-left\"></div>").append($("<div class=\"thumbnail-img\"></div>").append($("<img />").attr("src", file.thumbnailUrl).attr("big-src", file.url))).append($(delBtn)));
				                                            input.val(input.val()+";"+file.key);
				                                            input.trigger("blur");
				                                        }
				                                    });
				                                }
JS
			                                )]) . '<div id="pay_images-list"><div class="pay-images-list-upload"><div><span><i></i></span></div><span class="pay-images-list-upload-btn">上传</div></div>'
			                        ?>
			                        <?= $field ?>
		                        </div>
                    		</div>
                    		<div class="receipt-list-item">
                    			<?= $form->field($receiptModel, 'remark')->textarea(['placeholder'=>'请填写回款备注明细 (诸如业务明细之类) ...']); ?>
                    		</div>
                    		<div class="receipt-list-item">
                    			<div class="receipt-item-people clearfloat">
                    				<p>审核人：<span id="auditPeople"></span></p>
                    				<p>申请人：<span id="applyPeople"><?php $administrator = Yii::$app->user->identity; echo $administrator->name; ?></span></p>
                    			</div>
                    		</div>
                    	</div>
                    	<div class="receipt-check">
                    		<div style="margin-top: 15px;">
                    			<?= $form->field($receiptModel, 'is_send_sms')->checkbox(['readonly'=>true]) ?>
                    		</div>
                    		<div class="receipt-check-separate clearfloat" style="margin-top: 15px;">
                    			<?= $form->field($receiptModel, 'is_separate_money')->checkbox(['readonly'=>true]) ?>
                    			<p>勾选后，此次回款金额将按照子订单剩余应付金额占虚拟订单剩余应付金额的比例自动计算分配每个子订单的已付金额。</p>
                    		</div>
                    		<?= \yii\bootstrap\Html::activeHiddenInput($receiptModel, 'financial_code')?>
	                        <?= \yii\bootstrap\Html::activeHiddenInput($receiptModel, 'virtual_order_id')?>
	                        <?= \yii\bootstrap\Html::activeHiddenInput($receiptModel, 'company_id')?>
                    	</div>
                    </div>
                    <h4>提交记录</h4>
                    <div class="receipt-modal-bottom">
                        <table class="table table-striped table-hover receipt-record-list">
                            <thead>
                            <tr>
                                <th class="col-sm-3">提交时间</th>
                                <th>回款金额</th>
                                <th>凭证图片</th>
                                <th>操作人</th>
                            </tr>
                            </thead>
                            <tbody class="receipt-data"></tbody>
                            <tfoot>
                            <tr></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="submit" class="btn btn-primary receipt-sure-btn">提交审核</button>
                </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
                <?php
                $deleteUrl = Url::to(['receipt/delete-image']);
                $createUrl = Url::to(['receipt/create']);
                $reviewUrl = Url::to(['receipt/review']);
                $paymentAmountUrl = Url::to(['receipt/payment-amount']);
                $receiptModalTemplate = '<tr><td>{time}</td><td>{payment_amount}</td><td>{pay_images}</td><td>{creator_name}</td></tr>';
                $this->registerJs(<<<JS
                    $('#receipt-invoice').append('<option value="1" selected>开票</option><option value="2">不开票</option>');
                     $(function()
                     { 
                        $('#receipt-order-form').find("input[type='checkbox']").click(function()
                        { 
                            this.checked = !this.checked; 
                        }); 
                     });
                    $("#pay_images-list .pay-images-list-upload-btn").on("click",function(){
                        $('.receipt-item-upload').find('.btn-upload').click();
                    });
                    $('#pay_images-list').on('click', '.delete-receipt-image', function() {
                        var key = $(this).attr('data-key');
                        var _this = $(this);
                        $.post('{$deleteUrl}', {key: key}, function(rs){
                            if(rs['status'] === 200)
                                _this.parent().remove();
                            //获取所有val值；
                            var str= $('#pay_images').attr('value');
                            var resVal=str.replace(";"+key, "");
                             $('#pay_images').val(resVal);
                        }, 'json');
                    });
                    $("#pay_images-list").on("click",'img',function(){
                        var src = $(this).attr('src');
                        $(this).attr('src', $(this).attr('big-src'));
                        $(this).attr('big-src', src);
                        $(this).toggleClass("range");
                    });
                    /*$("#pay_images-list").on("click",".range",function(){
                        var src = $(this).attr('big-src');
                        $(this).attr('big-src', src);
                        $(this).attr('src', $(this).attr('src'));
                        $(this).removeClass("range");
                    });*/
                    $('.receipt-btn').click(function(){
                        var form = $('#receipt-order-form');
                        form.trigger('reset.yiiActiveForm');
                        form.find('.warning-active').text('');
                        
                        var virtual_order_id = $(this).attr('data-id');
                        var financial_code = $(this).attr('data-financial-code');
                        var company_id = $(this).attr('data-company-id');
                        var receiptModal = $('#receipt-modal');
                        var receiptModalTemplate = '{$receiptModalTemplate}';
                        receiptModal.find('table tbody').empty();
                        $('#receipt-virtual_order_id').val(virtual_order_id);
                        $('#receipt-company_id').val(company_id);
                        var total = $(this).attr('data-total');
                        var need = $(this).attr('data-need');
                        var paid = $(this).attr('data-paid');
                        // $('#receipt-payment_amount').val(need);
                        form.find('.field-receipt-financial_code .form-control-static').text(financial_code);
                        $('#receipt-financial_code').val(financial_code);
                        $('#receipt-money-info').text('订单应付金额：'+total+'元；已付金额：'+paid+'元；待付金额：'+need+'元；');
                        $.get('{$paymentAmountUrl}', {virtual_order_id: virtual_order_id}, function(rs){
                            if(rs.status == 200)
                            {
                                receiptModal.find('table tbody').empty();
                                $('#receipt-payment_amount').val(rs.payment_amount);
                                var resu=rs.result.BusinessSubject;

                                if(resu==false){ $('#bussnessName').text('--'); $('#financialNumber').text('--');}

                                 $.each(resu,function (val){

                                    if(resu[val].company_name){
                                         $('#bussnessName').text(resu[val].company_name);
                                    }else if(resu[val].region){                       
                                          $('#bussnessName').text(resu[val].region);
                                    } else{
                                        $('#bussnessName').text('--');
                                    }   

                                if(!resu[val].serial_number){
                                     $('#financialNumber').text('--');
                                }else{                       
                                     $('#financialNumber').text(resu[val].serial_number);  
                                } 
                                });
                                $('#orderWay').text(rs.result.way);
                                $('#virtualNumber').text(rs.result.virtualNumber);
                                $('#orderWay').text(rs.result.way);
                                $('#receipt-money-info').append(' 新建回款审核中金额：'+rs.new_payment_amount+'元；');
                                var models = rs.models;
                                for(var i = 0; i < models.length; i++)
                                {
                                    var pay_images = '';
                                    if(models[i]['pay_images'] != '')
                                    {
                                        pay_images = models[i]['pay_images'];
                                    }
                                    var item = receiptModalTemplate.replace('{time}', models[i]['created_at'])
                                        .replace('{payment_amount}', models[i]['payment_amount'])
                                        .replace('{pay_images}', pay_images)
                                        .replace('{creator_name}', models[i]['creator_name']);
                                        receiptModal.find('table tbody').append(item);
                                }
                            }
                            else
                            {
                                form.find('.warning-active').text(rs.message);
                            }
                        }, 'json');
                    });
                    $('#receipt-order-form').on('beforeSubmit', function(){
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
                    
                    $(".receipt-data").on("click",'img',function(){
                        var src = $(this).attr('src');
                        $(this).attr('src', $(this).attr('big-src'));
                        $(this).attr('big-src', src);
                        $(this).toggleClass("range");
                    });
JS
                ) ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<!--新建回款弹框end-->