<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use common\models\PayRecord;
use common\utils\Decimal;
use yii\bootstrap\Html;
use yii\helpers\Url;
use zhuravljov\yii\widgets\DateTimePicker;

/* @var $provider yii\data\ActiveDataProvider */

$this->title = '待确认回款';
$this->params['breadcrumbs'][] = $this->title;
/** @var common\models\Receipt[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
$imageStorage = Yii::$app->get('imageStorage');

?>
<div class="row">
    <div class="col-xs-12">
        <div class="ibox">
            <div class="ibox-content">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>提交回款时间</th>
                        <th>虚拟订单号</th>
                        <th>子订单号</th>
                        <th>客户信息</th>
                        <th>业务人员</th>
                        <th>客服人员</th>
                        <th>支付信息</th>
                        <th>回款信息</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <td><?= Yii::$app->formatter->asDatetime($model->created_at); ?></td>
                            <td><?= $model->virtualOrder->sn; ?></td>
                            <td>
                                <?php foreach($model->virtualOrder->orders as $order): ?>
                                    <p><?= $order->sn; ?> <a class="order-info-btn" data-target="#order-info-modal" data-toggle="modal" data-id="<?= $order->id; ?>">查看</a></p>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <p><a href="<?= Url::to(['user/info', 'id' => $model->virtualOrder->user_id ])?>"><?= $model->virtualOrder->user->name; ?></a></p>
                                <p><?= $model->virtualOrder->user->phone; ?></p>
                            </td>
                            <td>
                                <?php foreach($model->virtualOrder->orders as $order): ?>
                                    <p><?= $order->salesman_name; ?></p>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach($model->virtualOrder->orders as $order): ?>
                                    <p><?= $order->customer_service_name; ?></p>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <p>商品金额：<?= Decimal::formatCurrentYuan($model->virtualOrder->total_original_amount, 2) ?></p>
                                <p>优惠金额：<?= Decimal::formatCurrentYuan($model->virtualOrder->total_remit_amount, 2) ?></p>
                                <p>应付金额：<?= Decimal::formatCurrentYuan($model->virtualOrder->total_amount, 2) ?></p>
                                <p>调整金额：<?= Decimal::formatCurrentYuan($model->virtualOrder->adjust_amount, 2) ?></p>
                                <p>已付金额：<?= Decimal::formatCurrentYuan($model->virtualOrder->payment_amount, 2) ?></p>
                                <p>未付金额：<?= Decimal::formatCurrentYuan($model->virtualOrder->getPendingPayAmount(), 2) ?></p>
                            </td>
                            <td>
                                <p>回款日期：<?= Yii::$app->formatter->asDate($model->receipt_date) ?></p>
                                <p>回款方式：<?= $model->getPayMethodName() ?></p>
                                <p class="text-danger">回款金额：<?= $model->payment_amount ?></p>
                            </td>
                            <td style="vertical-align: middle; text-align: center;">
                                <button class="review-btn btn btn-primary btn-xs"
                                    <?php if($model->virtualOrder->hasPendingAdjustPriceOrder()): ?> disabled="disabled"<?php else: ?>
                                        data-target="#review-modal"
                                        data-toggle="modal"
                                        data-id="<?= $model->id; ?>"
                                        data-remark="<?= $model->remark; ?>"
                                        data-financial_code="<?= $model->financial_code; ?>"
                                    <?php endif; ?>>
                                    确认回款</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="9">
                            <?= LinkPager::widget([ 'pagination' => $pagination ]); ?>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="order-info-modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">订单信息</h4>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>

<?php
$order_info_url = \yii\helpers\Url::to(['order-info']);
$this->registerJs(<<<JS
    $('.order-info-btn').on('click',function(){
	    var id = $(this).attr('data-id');
	    var modal = $('#order-info-modal');
	    modal.find('.modal-body').empty();
        $.getJSON('{$order_info_url}', {id: id}, function(rs){
            if(rs.status !== 200)
            {
                modal.find('.modal-body').html('找不到该订单！');
            }
            else
            {
               var data = '<table class="table table-bordered">'+
                            '<thead>'+
                            '<th>商品信息</th>'+
                            '<th>业务人员</th>'+
                            '<th>订单状态</th>'+
                            '</thead>'+
                            '<tbody>'+
                            '<tr>'+
                            '<td>'+rs['order']['product_name']+'<br>'+rs['order']['area']+'<br>'+rs['order']['company_name']+'</td>'+
                            '<td>'+rs['order']['salesman_name']+'</td>'+
                            '<td>'+rs['order']['status']+'</td>'+
                            '</tr>'+
                            '</tbody>'+
                            '</table>';
               modal.find('.modal-body').html(data);
            }
        });
	});
JS
);
?>
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
<div class="modal fade new-receipt" id="review-modal" role="dialog" aria-labelledby="review-modal-label" data-type="">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="review-modal-label">回款审核</h4>
            </div>
                <?php
                $receiptModel = new \common\models\Receipt();
//                $receiptModel->status = \common\models\Receipt::STATUS_YES;
                $receiptModel->receipt_date = date('Y-m-d');
                $receiptModel->is_separate_money = 1;
                $receiptModel->is_send_sms = 1;
                $form = \yii\bootstrap\ActiveForm::begin([
                    'action' => ['receipt/review'],
                    'id' => 'receipt-order-form',
                    // 'validationUrl' => ['receipt/validation'],
//                    'enableAjaxValidation' => true,
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
                                <div class="receipt-header-text" id="createdAt"></div>
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
                    				<?= $form->field($receiptModel, 'payment_amount')->textInput(['readonly' => 'true']) ?>
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
		                        	<?= $form->field($receiptModel, 'receipt_company')->textInput(['readonly' => 'true']) ?>
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
                        			<?= $form->field($receiptModel, 'invoice')->dropDownList([]) ?>
                    			</div>
                    		</div>
                			<div class="receipt-list-item">
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
						                                            $("#pay_images-list").append($("<div class=\"thumbnail pull-left\"></div>")
						                                                .append($("<div class=\"thumbnail-img\"></div>").append($("<img />").attr("src", file.thumbnailUrl).attr("big-src", file.url))).append($(delBtn)));
						                                            input.val(input.val()+";"+file.key);
						                                            input.trigger("blur");
						                                        }
						                                    });
						                                }
JS
				                            )]) . '<div id="pay_images-list"></div>'
				                    ?>
				                    <?= $field ?>
                				</div>
                			</div>
                			<div class="receipt-list-item">
                    			<?= $form->field($receiptModel, 'remark')->textarea() ?>
                    		</div>
                    		<div class="receipt-list-item">
                    			<?= $form->field($receiptModel, 'audit_note')->textarea(['placeholder'=>'请填写审核意见...'])->label('审核备注<span class="text-danger">*</span>') ?>
                    		</div>
                    		<div class="receipt-list-item">
                    			<?= $form->field($receiptModel, 'password')->passwordInput(['name' => 'password'])->label('审核密码<span class="text-danger">*</span>'); ?>
                    		</div>
                    		<div class="receipt-list-item">
                    			<div class="receipt-item-people clearfloat">
                    				<p>审核人：<span id="auditPeople"><?php $administrator = Yii::$app->user->identity; echo $administrator->name; ?></span></p>
                    				<p>申请人：<span id="applyPeople"></span></p>
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
                    		<?= \yii\bootstrap\Html::activeHiddenInput($receiptModel, 'virtual_order_id')?>
		                    <?= \yii\bootstrap\Html::activeHiddenInput($receiptModel, 'id', ['name' => 'receipt_id'])?>
		                    <?= \yii\bootstrap\Html::activeHiddenInput($receiptModel, 'company_id')?>
		                    <?= \yii\bootstrap\Html::activeHiddenInput($receiptModel, 'status')?>
                    	</div>
                	</div>
                    <!--<?= $form->field($receiptModel, 'financial_code')->staticControl() ?>-->
                    
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
	                        <tfoot><tr></tr></tfoot>
	                    </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
<!--                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>-->
                    <button type="submit" class="btn btn-danger receipt-cancel-btn" data-id=" " name="type" value="2">审核不通过</button>
                    <button type="submit" class="btn btn-primary receipt-sure-btn" name="type" value="1">审核通过</button>
                </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
                <?php
                $deleteUrl = Url::to(['receipt/delete-image']);
                $createUrl = Url::to(['receipt/create']);
                $reviewUrl = Url::to(['receipt/review']);
                $this->registerJs(<<<JS
                    $('#pay_images-list').on('click', '.delete-receipt-image', function() {
                        var key = $(this).attr('data-key');
                        var _this = $(this);
                        $.post('{$deleteUrl}', {key: key}, function(rs){
                            if(rs['status'] === 200)
                                _this.parent().remove();
                        }, 'json');
                    });
                    //$("#pay_images-list").unbind('click');
                    $("#pay_images-list").on("click",'img',function(){
                        var src = $(this).attr('src');
                        $(this).attr('src', $(this).attr('big-src'));
                        $(this).attr('big-src', src);
                        $(this).toggleClass("range");
                    });

                    $('.receipt-btn').click(function(){
                        $('#receipt-virtual_order_id').val($(this).attr('data-id'));
                    });
                    var btnNum=0;
                    $('.receipt-sure-btn').click(function() {                         
                        $("#receipt-receipt_date").removeAttr("disabled");
                        $('#receipt-order-form').attr('data-type', '1');
                        $('#receipt-status').val('1');
                        btnNum++;
                        if(btnNum == 1){
                        	$(this).attr('disabled','disabled').text('审核中...');
                        	var form = $('#receipt-order-form');
                        	$.post(form.attr('action'), form.serialize(), function(rs){
	                            if(rs.status === 200)
	                            {
	                                //form.trigger('reset.yiiActiveForm');
	                                window.location.reload();
	                            }
	                            else
	                            {
	                            	btnNum=0;
	                                form.find('.warning-active').text(rs.message);
	                                form.find('.receipt-sure-btn').removeAttr('disabled').text('审核通过');
	                                form.find('.receipt-cancel-btn').removeAttr('disabled').text('审核不通过');
	                            }
	                        }, 'json');
	                        return false;
                        }
                    });
                    var btnNums=0;
                    $('.receipt-cancel-btn').click(function(){                         
                        $("#receipt-receipt_date").removeAttr("disabled");
                        $('#receipt-order-form').attr('data-type', '2');
                        $('#receipt-status').val('2');
                        btnNums++;
                        if(btnNums == 1){
                        	$(this).attr('disabled','disabled').text('取消审核中...');
                        	var form = $('#receipt-order-form');
                        	$.post(form.attr('action'), form.serialize(), function(rs){
	                            if(rs.status === 200)
	                            {
	                                //form.trigger('reset.yiiActiveForm');
	                                window.location.reload();
	                            }
	                            else
	                            {
	                            	btnNums=0;
	                                form.find('.warning-active').text(rs.message);
	                                form.find('.receipt-sure-btn').removeAttr('disabled').text('审核通过');
	                                form.find('.receipt-cancel-btn').removeAttr('disabled').text('审核不通过');
	                            }
	                        }, 'json');
	                        return false;
                        }
                    });
JS
                ) ?>
        </div>
    </div>
</div>

<?php
$info_url = \yii\helpers\Url::to(['info']);
$receiptModalTemplate = '<tr><td>{time}</td><td>{payment_amount}</td><td>{pay_images}</td><td>{creator_name}</td></tr>';
$this->registerJs(<<<JS
     $('#receipt-invoice').append('<option value="1" selected>开票</option><option value="2">不开票</option>');
     $("#receipt-invoice").attr('disabled','disabled');
     $("#receipt-receipt_date").attr('disabled','disabled');
     $("#receipt-pay_account").attr('readonly','readonly');
     $("#receipt-pay_method").attr('disabled','disabled');
     $("#receipt-remark").attr('disabled','disabled');
     $("#receipt-is_send_sms").attr('disabled','disabled');
     $("#receipt-is_separate_money").attr('disabled','disabled');
     $('.review-btn').on('click',function(){
        $('#receipt-order-form').trigger('reset.yiiActiveForm');
        $("#pay_images-list").empty();
	    var id = $(this).attr('data-id');
	    var remark = $(this).attr('data-remark');
	    var financial_code = $(this).attr('data-financial_code');
	    var modal = $('#review-modal');
	    modal.find('.field-receipt-remark .form-control-static').text(remark);
	    modal.find('.field-receipt-financial_code .form-control-static').text(financial_code);
        var receiptModalTemplate = '{$receiptModalTemplate}';
        modal.find('table tbody').empty();
	    $('.receipt-cancel-btn').attr('data-id', id);
        $.getJSON('{$info_url}', {id: id}, function(rs){
            if(rs.status !== 200)
            {
                modal.find('.modal-body').html('找不到该回款信息！');
            }
            else
            {
                $('#receipt-payment_amount').val(rs['model']['payment_amount']);
                $('#receipt-pay_method').val(rs['model']['pay_method']);
                $('#receipt-receipt_date').val(rs['model']['receipt_date']);
                $('#receipt-pay_account').val(rs['model']['pay_account']);
                $('#receipt-receipt_company').val(rs['model']['receipt_company']);
                var resu=rs.result.BusinessSubject;
                $.each(resu,function (val){


                                   if(resu[val].company_name){
                                         $('#bussnessName').text(resu[val].company_name);
                                    }else if(resu[val].region){                       
                                          $('#bussnessName').text(resu[val].region);
                                    } else{
                                        $('#bussnessName').text('--');
                                    }   

                  
                    if(resu[val].serial_number == null){
                         $('#financialNumber').text('--');
                    }else{                       
                         $('#financialNumber').text(resu[val].serial_number);  
                    } 
                });
                $('#orderWay').text(rs.result.way);
                $('#virtualNumber').text(rs.result.virtualNumber);
                $('#orderWay').text(rs.result.way);
                $('#createdAt').text(rs.result.createdAt);
                $('#receipt-remark').text(rs.result.remark);
                $('#applyPeople').text(rs.result.applyPeople);
                $('#receipt-invoice').val(rs.result.invoice);

                $('#receipt-money-info').text('订单应付金额：'+rs.result.total_amount+'元；已付金额：'+rs.result.vPaymentAmount+'元；待付金额：'+rs.result.remaining_amount+'元 ；新建回款审核中金额：'+rs.result.payment_amount+'元；');
                var val = '';
                var delBtn, input;
                for(var i in rs['model']['pay_images'])
                {
                    if(rs['model']['pay_images'][i]['key'] != '')
                    {
                        val += rs['model']['pay_images'][i]['key']+';';
                        // console.log(rs['model']['pay_images'][i]['url']);
                        delBtn = '<span class="delete-receipt-image btn btn-xs btn-danger" disabled data-key="'
                        +rs['model']['pay_images'][i]['key']+'">删除</span>';
                        $("#pay_images-list").append($("<div class=\"thumbnail pull-left\"></div>").append($("<div class=\"thumbnail-img\"></div>").append($("<img />").attr("src", rs['model']['pay_images'][i]['thumbnailUrl']).attr("big-src", rs['model']['pay_images'][i]['url']))));
                    }
                }
                if(val.length > 0)
                {
                    val = val.substring(0, val.length-1);
                }
                $("#pay_images").val(val);
                $('#receipt-virtual_order_id').val(rs['model']['virtual_order_id']);
                $('#receipt-company_id').val(rs['model']['company_id']);
                $('#receipt-id').val(rs['model']['id']);
                
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
                        modal.find('table tbody').append(item);
                }
            }
        });
        $("#pay_images-list .pay-images-list-upload-btn").on("click",function(){
            $('.receipt-item-upload').find('.btn-upload').click();
        });
        $(".receipt-data").unbind("click");
        $(".receipt-data").on("click",'img',function(){
            var src = $(this).attr('src');
            $(this).attr('src', $(this).attr('big-src'));
            $(this).attr('big-src', src);
            $(this).toggleClass("range");
        });

	});
JS
);
?>
