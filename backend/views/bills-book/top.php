<?php
/**
 * Created by PhpStorm.
 * User: jiayongbo
 * Date: 2018/6/4
 * Time: 13:45
 */

use common\utils\Decimal;
use zhuravljov\yii\widgets\DateTimePicker;

/** @var \common\models\Administrator $model */
/** @var \common\models\OrderCalculateCollect $orderCalculateCollect */
/** @var \common\models\OrderPerformanceCollect $orderPerformanceCollect */
/** @var $year string */
/** @var $month string */
/** @var $profitRecord \common\models\MonthProfitRecord */
$id = Yii::$app->request->get('id');
$date = Yii::$app->request->get('date');
?>
<div class="tally-details">
	<div class="equiry-month">
        <?php
        $searchModel = new \backend\models\BillsPersonalSearch();
        $searchModel->date = $date ? $date : $year.'-'.$month;
        $labelOptions = ['labelOptions' => ['class' => false]];
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => '/bills-book/' . Yii::$app->controller->action->id.'?id=' .$id,
            'layout' => 'inline',
            'method' => 'get',
        ]); ?>
        <b>查询月份</b>
        <?= $form->field($searchModel, 'date')->widget(DateTimePicker::className(), [
            'clientOptions' => [
                'format' => 'yyyy-mm',
                'language' => 'zh-CN',
                'autoclose' => true,
                'minView' => 'year',
                'startView' => 'year',
            ],
            'clientEvents' => [],
            'options' => ['class' => 'form-control','autocomplete' =>'off','disableautocomplete'],
        ]) ?>
        <button type="submit" class="btn btn-primary">确定</button>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
	</div>
	<div class="tally-details-content row">
		<div class="col-lg-5">
	        <div class="personal-detail navy-bg clearfloat">
                <div class="personal-head-portrait">
                	<div>
                		<img src="<?= $model->getImageUrl(92, 92) ?>" class="img-circle" alt="profile">
                	</div>
                </div>
                <div class="personal-detail-content">
                	<div class="bills-personal-name clearfloat">
                		<h1><?= $model->name; ?></h1>
	                	<h3><?= $model->title; ?></h3>
                	</div>
	                <p>手机号：<?= $model->phone; ?></p>
	                <p>所属公司：<?= $model->company ? $model->company->name : '--'; ?></p>
	                <p>所属部门：<?= $model->department ? $model->department->name : '--'; ?></p>
                </div>
                <div class="work-state">
                	<p><?= $model->is_dimission ? '离职' : '在职'; ?></p>
                </div>
	        </div>
	        <div class="bills-month-detail clearfloat">
	            <div class="bills-month-order">
	                <!--<p>预计利润订单数据</p>-->
	                <div class="bills-month-order-item clearfloat">
	                	<p>参与预计利润计算订单数量：</p>
	                	<span><?= $orderCalculateCollect ? $orderCalculateCollect->order_count : 0; ?>单</span>
	                </div>
	                <div class="bills-month-order-item clearfloat">
	                	<p>参与预计利润计算的退款订单数量：</p>
	                	<span><?= $orderCalculateCollect ? $orderCalculateCollect->refund_order_count : 0; ?>单</span>
	                </div>
	                <div class="bills-month-order-item clearfloat">
	                	<p>参与预计利润计算的取消订单数量：</p>
	                	<span><?= $orderCalculateCollect ? $orderCalculateCollect->cancel_order_count : 0; ?>单</span>
	                </div>
	                <p class="bills-updatetime">更新时间：<?= $orderCalculateCollect ? date('Y-m-d H:i:s',$orderCalculateCollect->expect_profit_time) : '--'; ?></p>
	            </div>
	            <div class="bills-month-number">
	            	<h1><?= floatval($month); ?>月</h1>
	            </div>
	        </div>
	    </div>
	    <div class="col-lg-7">
	    	<div class="bills-income row">
	    		<div class=" col-lg-6">
	    			<div class="bills-item">
	    				<div class="bills-income-title">
	    					<h5>预计利润收入</h5>
	    				</div>
	    				<div class="bills-income-content">
	    					<div class="bills-income-data">
	    						<div class="bills-income-list clearfloat">
		    						<div class="bills-income-li" style="width: 100%;">
		    							<p>参与计算订单预计利润金额</p>
		    							<span><?= $orderCalculateCollect ? Decimal::formatYenCurrentNoWrap($orderCalculateCollect->order_expected_amount) : 0; ?></span>
		    						</div>
		    					</div>
		    					<div class="bills-income-list clearfloat">
		    						<div class="bills-income-li" style="width: 100%;">
		    							<p>更正订单预计利润金额</p>
		    							<span><?= $orderCalculateCollect ? Decimal::formatYenCurrentNoWrap($orderCalculateCollect->correct_expected_amount) : 0; ?></span>
		    						</div>
		    					</div>
	    					</div>
	    					<div class="bills-income-time">
	    						<p>截止时间：<?= $profitRecord ? date('Y-m-d H:i:s',$profitRecord->range_end_time) : '--';  ?></p>
	    					</div>
	    				</div>
			        </div>
	    		</div>
	    		<div class="col-lg-6">
			        <div class="bills-item">
	    				<div class="bills-income-title">
	    					<h5>提成收入</h5>
	    				</div>
	    				<div class="bills-income-content">
	    					<div class="bills-income-data">
	    						<div class="bills-income-list clearfloat">
		    						<div class="bills-income-li">
		    							<p>参与计算订单提成收入</p>
		    							<span><?= $orderPerformanceCollect ? $orderPerformanceCollect->total_performance_amount : 0; ?></span>
		    						</div>
		    						<div class="bills-income-li">
		    							<p>阶梯算法订单提成收入</p>
		    							<span><?= $orderPerformanceCollect ? $orderPerformanceCollect->ladder_amount : 0; ?></span>
		    						</div>
		    					</div>
		    					<div class="bills-income-list clearfloat">
		    						<div class="bills-income-li">
		    							<p>更正订单提成收入</p>
		    							<span><?= $orderPerformanceCollect ? $orderPerformanceCollect->correct_amount : 0; ?></span>
		    						</div>
		    						<div class="bills-income-li">
		    							<p>固定提点订单提成收入</p>
		    							<span><?= $orderPerformanceCollect ? $orderPerformanceCollect->fix_point_amount : 0; ?></span>
		    						</div>
		    					</div>
	    					</div>
	    					<div class="bills-income-time">
	    						<p>截止时间：<?= $profitRecord ? ($profitRecord->performance_end_time ? date('Y-m-d H:i:s',$profitRecord->performance_end_time) : '--') : '--';  ?></p>
	    					</div>
	    				</div>
			        </div>
		        </div>
	        </div>
	    </div>
	</div>
</div>