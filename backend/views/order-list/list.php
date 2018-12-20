<?php
use backend\models\ConfirmPayForm;
use backend\models\OrderSearch;
use backend\widgets\LinkPager;
use common\models\BusinessSubject;
use common\models\MonthProfitRecord;
use common\models\Order;
use common\models\OrderRemark;
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
?>
<div class="row">
    <div class="col-lg-12">
        <div class="tabs-container">
            <?php if(!in_array($actionUniqueId, ['order-list/need-refund', 'order-list/vest'])): //财务中的订单退款功能是独立的，任何时候都不显示上面的标签页
                $allCount = Order::getAllCount($administrator);
                $inServiceCount = Order::getInServiceCount($administrator);
                $completedCount = Order::getCompletedCount($administrator);
                $breakCount = Order::getBreakCount($administrator);
                $orderAuditedCount = Order::getOrderAuditedCount($administrator);
                $pendingPayCount = Order::getPendingPayCount($administrator);
                $unpaidCount = Order::getUnpaidCount($administrator);
                $pendingAssignCount = Order::getPendingAssignCount($administrator);
                $pendingServiceCount = Order::getPendingServiceCount($administrator);
                $timeoutCount = Order::getTimeoutCount($administrator);
                ?>
            <ul class="nav nav-tabs">
                <?php if (Yii::$app->user->can('order-list/all')): ?>
                    <li<?php if ($actionUniqueId == 'order-list/all'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['all']) ?>">全部订单<?php if($allCount > 0):?>(<?= $allCount;?>)<?php endif;?></a>
                    </li>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('order-list/refund')): ?>
                    <li<?php if ($actionUniqueId == 'order-list/refund'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['refund']) ?>">退款中<?php if($orderAuditedCount > 0):?><span class="text-danger">(<?= $orderAuditedCount;?>)</span><?php endif;?></a>
                    </li>
                <?php endif; ?>
                <?php if ( Yii::$app->user->can('order-list/pending-payment')): ?>
                    <li<?php if ($actionUniqueId == 'order-list/pending-payment'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['pending-payment']) ?>">待付款<?php if($pendingPayCount > 0):?><span class="text-danger">(<?= $pendingPayCount;?>)</span><?php endif;?></a>
                    </li>
                <?php endif; ?>
                <?php if ( Yii::$app->user->can('order-list/pending-payment')): ?>
                    <li<?php if ($actionUniqueId == 'order-list/unpaid'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['unpaid']) ?>">未付清<?php if($unpaidCount > 0):?><span class="text-danger">(<?= $unpaidCount;?>)</span><?php endif;?></a>
                    </li>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('order-list/pending-assign')): ?>
                    <li<?php if ($actionUniqueId == 'order-list/pending-assign'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['pending-assign']) ?>">待分配<?php if($pendingAssignCount > 0):?><span class="text-danger">(<?= $pendingAssignCount;?>)</span><?php endif;?></a>
                    </li>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('order-list/pending-service')): ?>
                    <li<?php if ($actionUniqueId == 'order-list/pending-service'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['pending-service']) ?>">待服务<?php if($pendingServiceCount > 0):?><span class="text-danger">(<?= $pendingServiceCount;?>)</span><?php endif;?></a>
                    </li>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('order-list/in-service')): ?>
                    <li<?php if ($actionUniqueId == 'order-list/in-service'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['in-service']) ?>">服务中<?php if($inServiceCount > 0):?><span class="text-danger">(<?= $inServiceCount;?>)</span><?php endif;?></a>
                    </li>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('order-list/completed')): ?>
                    <li<?php if ($actionUniqueId == 'order-list/completed'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['completed']) ?>">服务完成<?php if($completedCount > 0):?>(<?= $completedCount;?>)<?php endif;?></a>
                    </li>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('order-list/break')): ?>
                    <li<?php if ($actionUniqueId == 'order-list/break'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['break']) ?>">服务终止<?php if($breakCount > 0):?>(<?= $breakCount;?>)<?php endif;?></a>
                    </li>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('order-list/timeout')): ?>
                    <li<?php if ($actionUniqueId == 'order-list/timeout'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['timeout']) ?>">报警订单<?php if($timeoutCount > 0):?><span class="text-danger">(<?= $timeoutCount; ?>)</span><?php endif;?></a>
                    </li>
                <?php endif; ?>
            </ul>
            <?php elseif($actionUniqueId == 'order-list/need-refund'):
                $refundReviewCount = Order::getOrderRefundReviewCount($administrator);
                $needRefundCount = Order::getOrderNeedRefundCount($administrator);
                ?>
                <ul class="nav nav-tabs">
                    <li<?php if ($status == 'need-refund-review'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['need-refund', 'status' => 'need-refund-review']) ?>">待审核<?php if($refundReviewCount > 0):?><span class="text-danger">(<?= $refundReviewCount;?>)</span><?php endif;?></a>
                    </li>
                    <li<?php if ($status == 'need-refund'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['need-refund', 'status' => 'need-refund']) ?>">待退款<?php if($needRefundCount > 0):?><span class="text-danger">(<?= $needRefundCount;?>)</span><?php endif;?></a>
                    </li>
                    <li<?php if ($status == 'refunded'): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['need-refund', 'status' => 'refunded']) ?>">已退款</a>
                    </li>
                </ul>
            <?php endif; ?>
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
                            $form = ActiveForm::begin(['layout' => 'inline', 'method' => 'get', 'action' => ['order-list/' . Yii::$app->controller->action->id]]); ?>
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
                                
                                <!--订单业绩提点月-->
                                <div  class="select2-options">
	                                <b>订单业绩提点月</b>
	                                <?= $form->field($searchModel, 'settlement_month')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'year',
	                                        'startView' => 'year',
	                                    ],
	                                    'clientEvents' => [],
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:118px;'],
	                                ]) ?>
	                            </div>
	                            
	                            <!--预计成本录入-->
	                            <div  class="select2-options">
	                                <?= $form->field($searchModel, 'total_cost', $labelOptions)->widget(Select2Widget::className(), [
	                                    'selectedItem' => OrderSearch::getCost(),
	                                    'placeholderId' => '0',
	                                    'placeholder' => '请选择',
	                                    'options' => ['class' => 'form-control', 'prompt' => '请选择'],
	                                    'static' => true,
	                                    'width'=>'100'
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
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
	                                ]) ?>
	                                <?= $form->field($searchModel, 'end_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
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
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
	                                ]) ?>
	                                <?= $form->field($searchModel, 'first_pay_end_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
	                                ]) ?>
	                            </div>
	                            
	                            <!--服务开始时间-->
	                            <div  class="select2-options">
	                                <b>服务开始时间</b>
	                                <?= $form->field($searchModel, 'begin_service_start_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
	                                ]) ?>
	                                <?= $form->field($searchModel, 'begin_service_end_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
	                                ]) ?>
	                            </div>
	                            
	                            <!--服务结束时间-->
	                            <div  class="select2-options">
	                                <b>服务结束时间</b>
	                                <?= $form->field($searchModel, 'end_service_start_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
	                                ]) ?>
	                                <?= $form->field($searchModel, 'end_service_end_time')->widget(DateTimePicker::className(), [
	                                    'clientOptions' => [
	                                        'format' => 'yyyy-mm-dd',
	                                        'language' => 'zh-CN',
	                                        'autoclose' => true,
	                                        'minView' => 'month',
	                                    ],
	                                    'clientEvents' => [],
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
                                    ]) ?>
	                            </div>

                                <!--备注添加时间-->
                                <div  class="select2-options">
                                    <b>备注添加时间</b>
                                    <?= $form->field($searchModel, 'content_add_start')->widget(DateTimePicker::className(), [
                                        'clientOptions' => [
                                            'format' => 'yyyy-mm-dd',
                                            'language' => 'zh-CN',
                                            'autoclose' => true,
                                            'minView' => 'month',
                                        ],
                                        'clientEvents' => [],
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
                                    ]) ?>
                                    <?= $form->field($searchModel, 'content_add_end')->widget(DateTimePicker::className(), [
                                        'clientOptions' => [
                                            'format' => 'yyyy-mm-dd',
                                            'language' => 'zh-CN',
                                            'autoclose' => true,
                                            'minView' => 'month',
                                        ],
                                        'clientEvents' => [],
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
                                    ]) ?>
                                </div>

                                <!--派单时间筛选-->
                                <div  class="select2-options">
                                    <b>派单时间</b>
                                    <?= $form->field($searchModel, 'order_dispatch_time_start')->widget(DateTimePicker::className(), [
                                        'clientOptions' => [
                                            'format' => 'yyyy-mm-dd',
                                            'language' => 'zh-CN',
                                            'autoclose' => true,
                                            'minView' => 'month',
                                        ],
                                        'clientEvents' => [],
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
                                    ]) ?>
                                    <?= $form->field($searchModel, 'order_dispatch_time_end')->widget(DateTimePicker::className(), [
                                        'clientOptions' => [
                                            'format' => 'yyyy-mm-dd',
                                            'language' => 'zh-CN',
                                            'autoclose' => true,
                                            'minView' => 'month',
                                        ],
                                        'clientEvents' => [],
                                        'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete','style'=>'width:146px;margin-left:6px;'],
                                    ]) ?>
                                </div>
	                            
	                            <!--付款方式-->
	                            <div  class="select2-options">
	                                <?= $form->field($searchModel, 'is_installment', $labelOptions)->widget(Select2Widget::className(), [
	                                    'selectedItem' => OrderSearch::getInstallment(),
	                                    'placeholderId' => '0',
	                                    'placeholder' => '请选择',
	                                    'options' => ['class' => 'form-control', 'prompt' => '请选择'],
	                                    'static' => true,
	                                    'width'=>'100'
	                                ]) ?>
	                            </div>
	                            
	                            <div  class="select2-options">
	                            <?php if ($actionUniqueId == 'order-list/all' || $actionUniqueId == 'order-list/break'): ?>
                                    <?= $form->field($searchModel, 'break_reason', $labelOptions)->widget(Select2Widget::className(), [
                                        'selectedItem' => OrderSearch::getBreakReasons(),
                                        'placeholderId' => '0',
                                        'placeholder' => '请选择原因',
                                        'options' => ['class' => 'form-control', 'prompt' => '请选择原因'],
                                        'static' => true,
                                    ]) ?>
                                <?php endif; ?>
	                            </div>
                                <div class="select2-options" >
                                    <b>客户满意度</b>
                                    <?= $form->field($searchModel, 'is_satisfaction')->widget(Select2Widget::className(), [
                                        'selectedItem' => Order::getSatisfaction(),
                                        'placeholderId' => '0',
                                        'placeholder' => '请选择',
                                        'width' => '100',
                                        'options' => ['class' => 'form-control', 'prompt' => '请选择'],
                                        'static' => true,
                                    ]) ?>
                                </div>
                                <div class="select2-options" >
                                    <b>财务明细编号</b>
                                    <?= $form->field($searchModel, 'finance_num')->widget(Select2Widget::className(), [
                                        'selectedItem' => [1 => '已录入',2 => '未录入'],
                                        'placeholderId' => '0',
                                        'placeholder' => '请选择',
                                        'width' => '100',
                                        'options' => ['class' => 'form-control', 'prompt' => '请选择'],
                                        'static' => true,
                                    ]) ?>
                                </div>
                                <div class="select2-options" >
                                    <b>预计利润计算</b>
                                    <?= $form->field($searchModel, 'expected_profit_calculate')->widget(Select2Widget::className(), [
                                        'selectedItem' => [1 => '已计算',2 => '未计算'],
                                        'placeholderId' => '0',
                                        'placeholder' => '请选择',
                                        'width' => '100',
                                        'options' => ['class' => 'form-control', 'prompt' => '请选择'],
                                        'static' => true,
                                    ]) ?>
                                </div>
                                <div class="select2-options" >
                                    <b>实际利润查询</b>
                                    <?= $form->field($searchModel, 'actual_profit_calculate')->widget(Select2Widget::className(), [
                                        'selectedItem' => [1 => '已计算',2 => '未计算'],
                                        'placeholderId' => '0',
                                        'placeholder' => '请选择',
                                        'width' => '100',
                                        'options' => ['class' => 'form-control', 'prompt' => '请选择'],
                                        'static' => true,
                                    ]) ?>
                                </div>
                                <div class="select2-options" >
                                    <b>服务状态标记</b>
                                    <?= $form->field($searchModel, 'service_status')->widget(Select2Widget::className(), [
                                        'selectedItem' =>  Order::getServiceStatus(),
                                        'placeholderId' => '0',
                                        'placeholder' => '请选择服务状态标记',
                                        'width' => '170',
                                        'options' => ['class' => 'form-control', 'prompt' => '请选择服务状态标记'],
                                        'static' => true,
                                    ]) ?>
                                </div>

                                <div class="select2-options" >
                                    <?= $form->field($searchModel, 'remark_id', $labelOptions)->widget(Select2Widget::className(), [
                                        'serverUrl' => ['order-list/get-remark-list'],
                                        'itemsName' => 'remarkList',
                                        'selectedItem' => $searchModel->orderRemark ? [$searchModel->orderRemark->creator_id => $searchModel->orderRemark->creator_name] : [],
                                        'options' => ['prompt' => '请选择', 'class' => 'form-control'],
                                        'placeholderId' => '0',
                                        'placeholder' => '请选择',
                                        'width'=>'118',
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
	                                
	                                <?= $form->field($searchModel, 'status')->hiddenInput(['value' => $status]) ?>
	                                <button type="submit" class="btn btn-sm btn-primary m-t-n-xs">搜索</button>
                                     <div class="advanced-tag-reset" style="display: inline; float : none;">
                                        <a href="<?= Url::to([$actionUniqueId]);?>">重置</a>
                                    </div>
	                            </div>
                            </div>
                            <?php \yii\bootstrap\ActiveForm::end(); ?>
                        </div>
                    </div>
                    <div class="panel-body" style="padding: 0;margin-bottom: 36px;">
                    	<div class="table-responsive top-pagination" style="padding: 9px 20px;border-bottom: 1px solid #e7eaec;">
                    		<div class="row" style="margin: 0;">
                    			<div class="col-lg-3 pull-left" style="height:30px;width:auto;padding: 0;">
                    				<?php if (Yii::$app->user->can('order-list/export') && $actionUniqueId != 'order-list/vest'): ?>
	                                <a href="<?= Url::to(\yii\helpers\ArrayHelper::merge(['order-list/export'], Yii::$app->request->get())) ?>" class="btn btn-sm btn-primary">导出订单记录</a>
	                                <?php endif; ?>
	                                <?php if (Yii::$app->user->can('order/create')): ?>
	                                    <a href="<?= Url::to(['valet-order/create','status' => 1]) ?>" target="_blank" style="margin-left: 10px;" class="btn btn-sm btn-primary">创建订单</a>
	                                <?php endif; ?>

                                    <?php if (Yii::$app->user->can('order/update-order-service')): ?>
                                        <a href="javascript:;"  style="margin-left: 10px;" class="btn btn-sm btn-primary batch-customer_service-btn" data-target="#batch-customer-service-modal" data-toggle="modal">批量更换客服</a>
                                    <?php endif; ?>
									<?php if (Yii::$app->user->can('order/update-order-clerk')): ?>
					                    <?php if ($actionUniqueId == 'order-list/in-service'): ?>
					                    	<a href="javascript:;"  style="margin-left: 10px;" class="btn btn-sm btn-primary batch-clerk-btn" data-target="#batch-clerk-modal" data-toggle="modal">批量更换服务人员</a>
					                    <?php endif; ?>
                                        <?php if ($actionUniqueId == 'order-list/pending-service'): ?>
                                            <a href="javascript:;"  style="margin-left: 10px;" class="btn btn-sm btn-primary batch-clerk-btn" data-target="#batch-clerk-modal" data-toggle="modal">批量更换服务人员</a>
                                        <?php endif; ?>
                                        <?php if ($actionUniqueId == 'order-list/completed'): ?>
                                            <a href="javascript:;"  style="margin-left: 10px;" class="btn btn-sm btn-primary batch-clerk-btn" data-target="#batch-clerk-modal" data-toggle="modal">批量更换服务人员</a>
                                        <?php endif; ?>

					                <?php endif; ?>
                    			</div>
                    			<div class="col-lg-9 pull-right" style="width:auto;padding: 0;">
                    				<?=
		                            LinkPager::widget([
		                                'pagination' => $pagination
		                            ]);
		                            ?>
                    			</div>
                    		</div>
                    	</div>
                        <div class="table-responsive" style="padding: 20px 20px 0;">
                            
                            <table class="table table-bordered" id="order-table" style="border: none;margin: 0;">
                                <thead>
                                <tr style="border-top: 1px solid #e7eaec;">
                                	<th style="width:12px;">
                                		<input type="checkbox" class="check-all">
                                	</th>
                                    <th style="width: 163px;">订单信息</th>
                                    <th style="width: 136px;">客户信息</th>
                                    <th style="width: 203px;">商品信息</th>
                                    <th style="width: 164px;">业务人员</th>
                                    <th style="width: 91px;">客服人员</th>
                                    <th style="width: 91px;">服务人员</th>
                                    <th style="width: 86px;">付款方式</th>
                                    <th class="text-right" style="width: 186px;">支付信息</th>
                                    <th class="text-center" style="width: 126px;">订单状态</th>
                                    <th class="text-center" style="width: 91px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($models as $order):
                                    $virtualModel = $order->virtualOrder;
                                    ?>
                                        <?php if ($virtualModel):?>
                                        <tr>
											<td style="vertical-align: middle;">
												<input type="checkbox" class="check" data-id = "<?= $order->id ?>">
											</td>
                                            <!-- 订单信息 -->
                                            <td style="vertical-align: middle;">
                                                <p class="text-muted"><?= Yii::$app->formatter->asDatetime($virtualModel->created_at) ?></p>
                                                <p>
                                                    <?php if (Yii::$app->user->can('virtual-order-list/list')): ?>
                                                        <a href="<?= Url::to(['virtual-order/order', 'vid' => $virtualModel->id]) ?>" target="_blank"><?= $virtualModel->sn; ?></a>
                                                    <?php else: ?>
                                                        <?= $virtualModel->sn; ?>
                                                    <?php endif; ?>
                                                </p>
                                                <p>
                                                    <?php if (Yii::$app->user->can('order/info') && $order->hasDetail()): ?>
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
                                                <?php if ($order->user):?>
                                                <p><a href="<?= Url::to(['user/info', 'id' => $order->user_id ])?>"><?= $order->user->name; ?></a></p>
                                                <p><?= $order->user->phone; ?></p>
                                                <?php endif;?>
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
                                                <?php if (Yii::$app->user->can('order-action/change-salesman')): ?>
                                                    <?php if ($order->user):?>
                                                    <button class="btn btn-xs btn-link text-info change-salesman-btn"
                                                            data-target="#change-salesman-modal"
                                                            data-toggle="modal" data-sn="<?= $order->sn ?>"
                                                            data-user-id="<?= $order->user->id; ?>" data-id="<?= $order->id ?>">修改
                                                    </button>
                                                    <?php endif;?>
                                                <?php endif; ?>
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
                                                    <?php if (Yii::$app->user->can('order-action/change-salesman')): ?>
                                                        <div class="team-btn<?= $order->id ?>">
                                                            <?php if ($order->user):?>
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
                                                            <?php endif;?>
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
                                                        已付金额：<?= Decimal::formatCurrentYuan($order->payment_amount); ?></p>
                                                    <p <?php if ($order->getPendingPayAmount()): ?>class="text-danger"<?php else:?>class="text-primary"<?php endif; ?>>
                                                        未付金额：<?= Decimal::formatCurrentYuan($order->getPendingPayAmount()); ?></p>
                                                <?php if($virtualModel->isPendingPayment() && Yii::$app->user->can('order-action/adjust-price')): ?>
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
                                                    <?php if($actionUniqueId != 'order-list/apply'): ?>
                                                    <?php if (Yii::$app->user->can('follow-record/create')
                                                        && ($virtualModel->isPendingPayment() || ($virtualModel->isUnpaid() && $order->isUnpaid() && !$order->is_installment)
                                                                || ($virtualModel->isCanceled() && $virtualModel->hasFollowRecords()))): ?>
                                                        <span class="btn btn-xs btn-primary see-order-follow-record m-t-xs"
                                                              data-target="#order-follow-record-modal"
                                                              data-toggle="modal"
                                                              data-order-id="<?= $order->id ?>"
                                                              data-is-cancel="<?= $virtualModel->isCanceled() ? '1' : '0'?>"
                                                              data-id="<?= $order->virtual_order_id ?>">跟进记录</span>
                                                    <?php endif; ?>
                                                    <?php if ($virtualModel->isPendingPayment() || $virtualModel->isUnpaid() && !$virtualModel->hasRefund()): ?>
                                                        <?php if (Yii::$app->user->can('order-action/confirm-pay')): ?>
                                                            <span class="btn btn-xs btn-warning confirm-pay-btn m-t-xs"
                                                                  data-target="#confirm-pay-modal"
                                                                  data-toggle="modal"
                                                                  data-order-id="<?= $order->id ?>"
                                                                  data-id="<?= $order->virtual_order_id ?>">确认付款</span>
                                                        <?php endif; ?>
                                                        <?php if (Yii::$app->user->can('receipt/create')): ?>
                                                            <span class="btn btn-xs btn-warning receipt-btn m-t-xs"
                                                                  data-target="#receipt-modal"
                                                                  data-toggle="modal"
                                                                  data-id="<?= $order->virtual_order_id ?>"
                                                                  data-financial-code="<?= $virtualModel->contract ? $virtualModel->contract->serial_number : '--'; ?>"
                                                                  data-company-id="<?= $order->company_id ?>"
                                                                  data-total="<?= $virtualModel->total_amount; ?>"
                                                                  data-paid="<?= $virtualModel->payment_amount; ?>"
                                                                  data-need="<?= $virtualModel->getPendingPayAmount(); ?>">
<!--                                                                    --><?php //if ($virtualModel->getPendingPayAmount() > 0):?>
                                                                        <?= $virtualModel->getReceiptStatusName()?>
<!--                                                                    --><?php //endif;?>
                                                            </span>
                                                        <?php endif; ?>

                                                        <?php if ($order->next_follow_time && !$order->isPayAfterService()): ?>
                                                            <?php if (Yii::$app->user->can('follow-record/create')): ?>
                                                                <div class="<?php if ($order->isWarning()): ?>text-danger<?php else: ?>text-success<?php endif; ?> m-t-xs">
                                                                    下次跟进：<span
                                                                            title="<?= Yii::$app->formatter->asDatetime($order->next_follow_time, 'yyyy-MM-dd HH:00') ?>"><?= Yii::$app->formatter->asRelativeTime($order->next_follow_time) ?></span>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if ($virtualModel->isCanceled()): ?>
                                                        <?php if ($virtualModel->hasRefund() && !$virtualModel->isRefunded()): ?>
                                                            <?php if (Yii::$app->user->can('refund/do')): ?>
                                                                <a href="<?= Url::to(['refund-record/list', 'virtual_order_id' => $order->virtual_order_id]) ?>"
                                                                   class="btn btn-xs btn-primary m-t-xs">财务退款</a>
                                                            <?php endif; ?>
                                                        <?php elseif ($virtualModel->hasRefund()): ?>
                                                            <?php if (Yii::$app->user->can('refund/do')): ?>
                                                                <a href="<?= Url::to(['refund-record/list', 'virtual_order_id' => $order->virtual_order_id]) ?>"
                                                                   class="btn btn-xs btn-link text-muted m-t-xs">已退款</a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if ($order->isPendingService() && !$order->isCancel() && !$order->isRefundApply() && !$order->isRefundAudit()): ?>
                                                        <?php if (Yii::$app->user->can('order-action/start-service')): ?>
                                                            <span class="btn btn-xs btn-primary start-service-btn m-t-xs"
                                                                  data-target="#order-start-service-modal"
                                                                  data-toggle="modal"
                                                                  data-product-name="<?= $order->product_name?>"
                                                                  data-customer-service-name="<?= $order->customerService ? $order->customerService->name : ''?>"
                                                                  data-customer-service-phone="<?= $order->customerService ? $order->customerService->phone : ''?>"
                                                                  data-supervisor-phone="<?= $order->supervisor ? $order->supervisor->phone : ''?>"
                                                                  data-id="<?= $order->id ?>">开始服务</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if ($order->isPendingAllot() && !$order->isCancel() && !$order->isRefundApply() && !$order->isRefundAudit()): ?>
                                                        <?php if (Yii::$app->user->can('order-action/change-clerk')): ?>
                                                            <span class="btn btn-xs btn-primary clerk-allot-btn m-t-xs"
                                                                  data-clerk-type="clerk_allot_type"
                                                                  data-target="#clerk-modal"
                                                                  data-toggle="modal" data-id="<?= $order->id ?>"
                                                                  data-product-id="<?= $order->product_id ?>"
                                                                  data-salesman="<?= $order->salesman_aid ?>"
                                                                  data-district-id="<?= $order->district_id ?>">派单</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if ($order->isRefundApply()): ?>
                                                        <?php if (Yii::$app->user->can('order-action/refund-review')): ?>
                                                            <span class="btn btn-xs btn-warning refund-audit-btn m-t-xs"
                                                                  data-target="#refund-order-modal"
                                                                  data-toggle="modal"
                                                                  data-id="<?= $order->id ?>">退款审核</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>

                                                    <?php if (!$virtualModel->isCanceled()): ?>
                                                        <?php if ($order->isRefundAudit()): ?>
                                                            <?php if (Yii::$app->user->can('refund/do')): ?>
                                                                <a href="<?= Url::to(['refund-record/list', 'virtual_order_id' => $order->virtual_order_id]) ?>"
                                                                   class="btn btn-xs btn-primary m-t-xs">财务退款</a>
                                                            <?php endif; ?>
                                                        <?php elseif ($order->isRefunded()): ?>
                                                            <?php if (Yii::$app->user->can('refund/do')): ?>
                                                                <a href="<?= Url::to(['refund-record/list', 'virtual_order_id' => $order->virtual_order_id]) ?>"
                                                                   class="btn btn-xs btn-link text-muted m-t-xs">已退款</a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php else: ?>
                                                        <?php if(Yii::$app->user->can('performance-statistics/*')):?>
                                                        <a href="<?= Url::to(['order/info', 'id' => $order->id,'sign' => 'show','#'=>'performance']) ?>"
                                                           class="btn btn-xs btn-primary m-t-xs">去计算提成</a>
                                                        <span class="btn btn-xs btn-danger reject-btn m-t-xs"
                                                              data-target="#order-apply-performance"
                                                              data-toggle="modal"
                                                              data-id="<?= $order->id ?>">计算提成驳回</span>
                                                        <?php endif;?>
                                                    <?php endif; ?>
                                                </td>
                                        </tr>
                                        <?php endif;?>
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
<?php
$this->registerJs(<<<JS
$(function(){
	var table = $('#order-table');
    $('.check-all').click(function(){
        var isChecked = $(this).prop('checked');
        var items = table.find('.check');
        items.prop('checked', isChecked);
    });
    table.find('.check').click(function(){
        var isAllChecked = true;
        var all = table.find('.check');
        var checkNum = 0;
        
        for(var i = 0; i < all.length; i++)
        {
            var isChecked = $(all[i]).prop('checked');
            if(isChecked) checkNum++;
        }
        if( all.length == checkNum)
        {
        	$('.check-all').prop('checked', true).prop('indeterminate', false);
        }
        else
        {
        	if(checkNum > 0){
        		$('.check-all').prop('indeterminate', true);
        	}else{
        		$('.check-all').prop('checked', false).prop('indeterminate', false);
        	} 
        }
    });
    
//	$("#untreated-modal").on('hide.bs.modal', function () {
//      $('#untreated-modal .error').empty();
//  })
//	$("#processed-modal").on('hide.bs.modal', function () {
//      $('#processed-modal .error').empty();
//  })
	
	
})


JS
)?>
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

<!--新建回款弹框start-->
<?php if (Yii::$app->user->can('virtual-order-action/receipt')): ?>
    <div class="modal fade" id="receipt-modal" role="dialog" aria-labelledby="modal-title">
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
                            'offset' => 'col-sm-offset-3',
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
                        <?= $form->field($receiptModel, 'payment_amount')->textInput() ?>
                        <p class="col-sm-8 col-sm-offset-3" id="receipt-money-info"></p>
                        <?= $form->field($receiptModel, 'receipt_date')->widget(DateTimePicker::className(), [
                            'clientOptions' => [
                                'format' => 'yyyy-mm-dd',
                                'language' => 'zh-CN',
                                'autoclose' => true,
                                'minView' => 'month',
                            ],
                        ]) ?>
                        <?= $form->field($receiptModel, 'pay_method')->dropDownList(\yii\helpers\ArrayHelper::merge(['' => '请选择回款方式'], PayRecord::getPayMethod())) ?>
                        <?= $form->field($receiptModel, 'pay_account')->textInput() ?>
                        <?= $form->field($receiptModel, 'receipt_company')->textInput() ?>

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
	                                                .append($("<img />").attr("src", file.thumbnailUrl).attr("big-src", file.url)).append($(delBtn)));
	                                            input.val(input.val()+";"+file.key);
	                                            input.trigger("blur");
	                                        }
	                                    });
	                                }
JS
                                )]) . '<div id="pay_images-list"></div>'
                        ?>
                        <?= $field ?>
                        <?= $form->field($receiptModel, 'is_separate_money')->checkbox(['readonly'=>true]) ?>
                        <div class="form-group field-batchadjustpriceform-adjust_price">
                            <label class="control-label col-sm-3"></label>
                            <div class="col-sm-8">（勾选后，此次回款金额将按照子订单剩余应付金额占虚拟订单剩余应付金额的比例自动计算分配每个子订单的已付金额。）</div>
                            <div class="help-block col-sm-12 col-sm-offset-3"></div>
                        </div>
                        <?= $form->field($receiptModel, 'is_send_sms')->checkbox(['readonly'=>true]) ?>
                        <?= $form->field($receiptModel, 'financial_code')->staticControl() ?>
                        <?= $form->field($receiptModel, 'remark')->textarea(); ?>
                        <?= \yii\bootstrap\Html::activeHiddenInput($receiptModel, 'financial_code')?>
                        <?= \yii\bootstrap\Html::activeHiddenInput($receiptModel, 'virtual_order_id')?>
                        <?= \yii\bootstrap\Html::activeHiddenInput($receiptModel, 'company_id')?>
                    </div>
                    <h4>提交记录</h4>
                    <div class="receipt-modal-bottom">
                        <table class="table table-striped table-hover receipt-record-list">
                            <thead>
                            <tr>
                                <th class="col-sm-3">时间</th>
                                <th>金额</th>
                                <th>凭证</th>
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
                     $(function()
                     { 
                        $('#receipt-order-form').find("input[type='checkbox']").click(function()
                        { 
                            this.checked = !this.checked; 
                        }); 
                     }); 
                    $('#pay_images-list').on('click', '.delete-receipt-image', function() {
                        var key = $(this).attr('data-key');
                        var _this = $(this);
                        $.post('{$deleteUrl}', {key: key}, function(rs){
                            if(rs['status'] === 200)
                                _this.parent().remove();
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
                        $('#receipt-money-info').text('订单应付金额：'+total+'元 已付金额：'+paid+'元 待付金额：'+need+'元');
                        $.get('{$paymentAmountUrl}', {virtual_order_id: virtual_order_id}, function(rs){
                            if(rs.status == 200)
                            {
                                receiptModal.find('table tbody').empty();
                                $('#receipt-payment_amount').val(rs.payment_amount);
                                $('#receipt-money-info').append(' 新建回款审核中金额：'+rs.new_payment_amount+'元');
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

<!--批量修改客服start-->
<div class="modal fade" id="batch-customer-service-modal" role="dialog" aria-labelledby="modal-title">
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
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary batch-customer_service-sure-btn">保存</button>
                </div>
            </div>
        </div>
        <?php
        $ajaxGetCustomerServiceListUrl = \yii\helpers\Url::to(['order/get-service']);//客服人员列表获取
        $ajaxBatchCustomerServiceListUrl = \yii\helpers\Url::to(['order/update-order-service']);//客服人员批量修改
        $this->registerJs(<<<JS
            $('.batch-customer_service-btn').click(function(){
                $('.change-customer-service-table table tbody').empty();
                $.ajax({
					type : 'post',
					url : '{$ajaxGetCustomerServiceListUrl}',
					data:{},
					async: false,
					dataType:'json',
					success : function(rs){
                        for(var i = 0;i < rs.length; i++) {
	                        var item = '<tr><td><input name="customer_service_id" type="radio" value="'+ rs[i].id +'" data-id="'+rs[i].administrator_id+'" /></td><td class="batch-customer-service-name">'+ rs[i].name +'</td><td>'+rs[i].servicing_number+'</td><td>'+rs[i].phone+'</td></tr>';
	                        $('#batch-customer-service-modal .change-customer-service-table table tbody').append(item);
	                    }
					}
			    });
            });
            
            $('.batch-customer_service-sure-btn').click(function(){
				var all = $('#order-table').find('.check');
				var radio = $('#batch-customer-service-modal table tbody tr td input');
 				var checkNum = 0;
				var orderId = [];
				var serviceId = 0;
				var administratorId = 0;
				var serviceName = '';
		        for(var i = 0; i < all.length; i++)
		        {
		            var isChecked = $(all[i]).prop('checked');
		            if(isChecked)
		            {
		            	checkNum++;
		            	var id = $(all[i]).attr('data-id');
		            	orderId.push(id);
		            }
		        }
		        for(var i = 0; i < radio.length; i++)
		        {
		            var isOff = $(radio[i]).prop('checked');
		            if(isOff)
		            {
		            	serviceId = $(radio[i]).val();
		            	administratorId = $(radio[i]).attr('data-id');
		            	serviceName = $('#batch-customer-service-modal table tbody tr .batch-customer-service-name').eq(i).text();
		            }
		        }
				if(checkNum > 0 && !(serviceId == 0)){
					$('#batch-customer-service-modal .modal-footer span').html('');
					$.ajax({
						type : 'post',
						url : '{$ajaxBatchCustomerServiceListUrl}',
						data:"order_id="+ orderId + "&service_id=" + serviceId + "&service_name=" + serviceName + "&administrator_id=" + administratorId,
						async: false,
						success : function(rs){
							if(rs.code === 200){
								window.location.reload();
							}
						}
				    });
				}else{
					if(!(checkNum > 0)){
						$('#batch-customer-service-modal .modal-footer span').html('请必须选择一个订单');
					}else if(serviceId == 0){
						$('#batch-customer-service-modal .modal-footer span').html('请必须选择一个客服');
					}
					
				}
				
			})
			$('#batch-customer-service-modal').on('hide.bs.modal', function () {
				$('#batch-customer-service-modal .modal-footer span').html('');
			})
JS
        ) ?>
    </div>
<!--批量修改客服end-->
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

<!--批量修改服务人员start-->
    <div class="modal fade" id="batch-clerk-modal" role="dialog" aria-labelledby="modal-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">更换服务人员</h4>
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
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary batch-clerk-sure-btn">保存</button>
                </div>
            </div>
        </div>
    </div>
        <?php
        $ajaxGetClerkListUrl = \yii\helpers\Url::to(['order/get-clerk']);//获取服务人员列表
        $ajaxBatchClerkListUrl = \yii\helpers\Url::to(['order/update-order-clerk']);//批量修改服务人员
        $this->registerJs(<<<JS
        	$('.batch-clerk-btn').click(function(){
                $('.change-customer-service-table table tbody').empty();
                $.ajax({
					type : 'post',
					url : '{$ajaxGetClerkListUrl}',
					data:{},
					async: false,
					dataType:'json',
					success : function(rs){
                        for(var i = 0;i < rs.length; i++) {
	                        var item = '<tr><td><input name="clerk_id" type="radio" value="'+ rs[i].id +'" data-id="'+rs[i].administrator_id+'" /></td><td class="batch-clerk-name">'+ rs[i].name +'</td><td>'+rs[i].phone+'</td><td>'+rs[i].province_name +'&nbsp;'+rs[i].city_name +'&nbsp;'+ rs[i].district_name +'&nbsp;'+ rs[i].address +'</td></tr>';
	                        $('#batch-clerk-modal .change-clerk-table table tbody').append(item);
	                    }
					}
			    });
            });
            
            $('.batch-clerk-sure-btn').click(function(){
				var all = $('#order-table').find('.check');
				var radio = $('#batch-clerk-modal table tbody tr td input');
 				var checkNum = 0;
				var orderId = [];
				var clerkId = 0;
				var administratorId = 0;
				var clerkName = '';
		        for(var i = 0; i < all.length; i++)
		        {
		            var isChecked = $(all[i]).prop('checked');
		            if(isChecked)
		            {
		            	checkNum++;
		            	var id = $(all[i]).attr('data-id');
		            	orderId.push(id);
		            }
		        }
		        for(var i = 0; i < radio.length; i++)
		        {
		            var isOff = $(radio[i]).prop('checked');
		            if(isOff)
		            {
		            	clerkId = $(radio[i]).val();
		            	administratorId = $(radio[i]).attr('data-id');
		            	clerkName = $('#batch-clerk-modal table tbody tr .batch-clerk-name').eq(i).text();
		            }
		        }
				if(checkNum > 0 && !(clerkId == 0)){
					$('#batch-clerk-modal .modal-footer span').html('');
					$.ajax({
						type : 'post',
						url : '{$ajaxBatchClerkListUrl}',
						data:"order_id="+ orderId + "&clerk_id=" + clerkId + "&clerk_name=" + clerkName + "&administrator_id=" + administratorId,
						async: false,
						success : function(rs){
							if(rs.code === 200){
								window.location.reload();
							}
						}
				    });
				}else{
					if(!(checkNum > 0)){
						$('#batch-clerk-modal .modal-footer span').html('请必须选择一个订单');
					}else if(clerkId == 0){
						$('#batch-clerk-modal .modal-footer span').html('请必须选择一个服务人员');
					}
					
				}
				
			})
			$('#batch-clerk-modal').on('hide.bs.modal', function () {
				$('#batch-clerk-modal .modal-footer span').html('');
			})
JS
        ) ?>
<!--批量修改服务人员end-->
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

<!--开始服务start-->
<?php if (Yii::$app->user->can('order-action/start-service')): ?>
    <div class="modal fade" id="order-start-service-modal" role="dialog" aria-labelledby="myModalLabel">
        <?php
        $startServiceForm = new \backend\models\StartServiceForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['order-action/start-service'],
            'id' => 'start-service-form',
            'validationUrl' => ['order-action/start-service', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-1',
                    'offset' => 'col-sm-offset-0',
                    'wrapper' => 'col-sm-11',
//                    'hint' => 'col-sm-offset-1 col-sm-11',
                ],
            ],
        ]);
        ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">开始服务</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($startServiceForm, 'is_send_sms')->checkbox() ?>
                    <div class="start-service-sms-preview" style="display: none">
                    <?php $start_service_sms_id = Property::get('start_service_sms_id'); ?>
                    <?php $start_service_sms_preview = Property::get('start_service_sms_preview'); ?>
                    <?php if(!empty($start_service_sms_id) && !empty($start_service_sms_preview)): ?>
                        <h4>将给客户发送以下信息：</h4>
                        <p id="start-service-sms-preview"></p>
                    <?php else:?>
                        <h4 id="start-service-sms-preview" class="text-danger"></h4>
                    <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($startServiceForm, 'order_id', ['id' => 'start-service-form_order_id']); ?>
                    <span class="text-danger warning-active"><?= Html::error($startServiceForm, 'order_id'); ?></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php
        $startServiceSmsPreview = Property::get('start_service_sms_preview');
        $startServiceSmsId = Property::get('start_service_sms_id');
        $this->registerJs(<<<JS
        $('#is_send_sms').click(function(){
            if($('#is_send_sms').is(':checked')){
                $(".start-service-sms-preview").show(); 
            }else{
                $(".start-service-sms-preview").hide(); 
            }
        });
        $('.start-service-btn').on('click', function(){
            $('.start-service-sms-preview').show();
            $('#start-service-form').trigger('reset.yiiActiveForm');
            var id = $(this).attr('data-id');
            var startServiceSmsPreview = '{$startServiceSmsPreview}';
            var startServiceSmsId = '{$startServiceSmsId}';
            $('#start-service-form_order_id').val(id);
            $('.warning-active').text('');
            //获取信息，进行替换短信参数
            if(!startServiceSmsPreview || !startServiceSmsId)
            {
                $('#start-service-sms-preview').text('短信尚未配置，快找产品经理！');
            }
            else
            {
                var productName = $(this).attr('data-product-name');
                var customerServiceName = $(this).attr('data-customer-service-name');
                var customerServicePhone = $(this).attr('data-customer-service-phone');
                var supervisorPhone = $(this).attr('data-supervisor-phone');
                startServiceSmsPreview = startServiceSmsPreview.replace('{1}', productName);
                startServiceSmsPreview = startServiceSmsPreview.replace('{2}', customerServiceName);
                startServiceSmsPreview = startServiceSmsPreview.replace('{3}', customerServicePhone);
                startServiceSmsPreview = startServiceSmsPreview.replace('{4}', supervisorPhone);
                $('#start-service-sms-preview').text(startServiceSmsPreview);
            }
        });
        $('#start-service-form').on('beforeSubmit', function(){
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
<!--开始服务end-->

<!--确认付款start-->
<?php if (Yii::$app->user->can('order-action/confirm-pay')): ?>
    <div class="modal fade" id="confirm-pay-modal" role="dialog" aria-labelledby="myModalLabel">
        <?php
        $confirmPayForm = new ConfirmPayForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['order-action/confirm-pay'],
            'id' => 'confirm-pay-form',
            'validationUrl' => ['order-action/confirm-pay', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-3',
//                    'offset' => 'col-sm-offset-1',
                    'wrapper' => 'col-sm-8',
                    'hint' => 'col-sm-1',
                ],
            ],
        ]);
        ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">确认付款</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <p class="col-sm-offset-3 col-sm-8">确定该笔订单款项已支付吗?</p>
                    </div>
                    <?= $form->field($confirmPayForm, 'confirm_payment_amount')->textInput()->hint('元') ?>
                    <?= $form->field($confirmPayForm, 'pay_method')->dropDownList(PayRecord::getPayMethod()) ?>
                    <?= $form->field($confirmPayForm, 'password')->passwordInput() ?>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($confirmPayForm, 'virtual_order_id', ['id' => 'confirm-pay-form_virtual_order_id']); ?>
                    <?= Html::activeHiddenInput($confirmPayForm, 'order_id', ['id' => 'confirm-pay-form_order_id']); ?>
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php
        $this->registerJs(<<<JS
    $('.confirm-pay-btn').on('click', function(){
        $('#confirm-pay-form').trigger('reset.yiiActiveForm');
        var id = $(this).attr('data-id');
        var order_id = $(this).attr('data-order-id');
        $('#confirm-pay-form_virtual_order_id').val(id);
        $('#confirm-pay-form_order_id').val(order_id);
    });
    $('#confirm-pay-form').on('beforeSubmit', function(){
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
<!--确认付款end-->

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
                    'hint' => 'col-sm-9 col-sm-offset-3',
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
                    <?= $form->field($adjustForm, 'adjust_price_reason')->textarea()->hint('（价格修改申请提交后，将手机短信通知订单负责业务员的部门主管，请耐心等候审核。）') ?>
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
            var form = $('#adjust-price-form');
            var adjust_price_origin_price = 0;
            function changeAdjustPrice()
            {
                var adjustPrice = parseFloat(form.find('[name=adjust_price]').val());
                if(isNaN(adjustPrice)) adjustPrice = 0;
                var price = parseFloat(adjust_price_origin_price) + adjustPrice;
                form.find('.field-price .form-control-static').text(fmoney(price));
            }
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
            var id = $(this).attr('data-id');
            var order_id = $(this).attr('data-order-id');
            var isCancel = $(this).attr('data-is-cancel');
            followRecordModal.find('.warning-active').empty();
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

<!--计算业绩驳回start-->
<?php if (Yii::$app->user->can('follow-record/create')): ?>
    <div class="modal fade" id="order-apply-performance" role="dialog" aria-labelledby="modal-title">
        <div class="modal-dialog" role="document">
            <?php
            $orderApplyRejectForm = new \backend\models\OrderApplyRejectForm();
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['order-action/reject'],
                'validationUrl' => ['order-action/reject', 'is_validate' => 1],
                'enableAjaxValidation' => true,
                'id' => 'order-reject-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-2',
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
                    <h4 class="modal-title">申请计算提成驳回确认</h4>
                </div>
                <div class="modal-body">
                    <p id="record-hint">确定要驳回此次提成计算申请吗？此操作发生后，将不可逆转。</p>
                    <div class="fol-form">
                        <?= Html::activeHiddenInput($orderApplyRejectForm,'order_id') ?>
                        <?= $form->field($orderApplyRejectForm, 'remark')->textarea() ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="delete-btn-record">
                        <button type="button" class="btn btn-default cancel-delete-record" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary" id="confirm-reject">确定驳回</button>
                    </div>
                </div>
            </div>
            <?php
            $ajaxFollowRecordInfoUrl = \yii\helpers\Url::to(['follow-record/ajax-list']);
            $this->registerJs(<<<JS
        $('.reject-btn').click(function() 
        {
            var id = $(this).attr('data-id');
            $('#orderapplyrejectform-order_id').val(id);
        })    
            
        $('#order-reject-form').on('beforeSubmit', function(){
            var form = $(this);
            $.post(form.attr('action'), form.serialize(), function(rs){
                if(rs.status === 200)
                {
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
    </div>
<?php endif; ?>
<!--计算业绩驳回end-->