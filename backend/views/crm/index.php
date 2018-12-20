<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/25
 * Time: 上午9:32
 */

use backend\assets\FlotAsset;
use common\models\CrmCustomer;
use common\models\CrmOpportunity;
use common\models\OrderCalculateCollect;
use common\models\VirtualOrder;
use common\utils\Decimal;

/* @var $this yii\web\View */

FlotAsset::register($this);
/** @var \common\models\MonthProfitRecord $profitRecord */
/** @var OrderCalculateCollect $orderCalculateCollect */
/** @var OrderCalculateCollect[] $model */
/** @var \common\models\OrderPerformanceCollect $orderPerformanceCollect */
/** @var \common\models\Administrator $administrator */
/** @var array $teams */
$administrator = Yii::$app->user->identity;
$month = date('m',time());
?>
<div class="wrapper wrapper-content animated fadeIn">
    <div class="ibox">
        <div class="ibox-title">
            <h5>系统首页</h5>
        </div>
        <div class="tally-details" style="border-top:1px solid #e7eaec;margin: 0;">
			<div class="tally-details-content row">
				<div class="col-lg-5">
			        <div class="personal-detail navy-bg clearfloat">
		                <div class="personal-head-portrait">
		                	<div>
		                		<img src="<?= $administrator->getImageUrl(100, 100) ?>" class="img-circle" alt="profile">
		                	</div>
		                </div>
		                <div class="personal-detail-content">
		                	<div class="bills-personal-name clearfloat">
		                		<h1><?= $administrator->name; ?></h1>
			                	<h3><?= $administrator->title; ?></h3>
		                	</div>
			                <p>手机号：<?= $administrator->phone; ?></p>
			                <p>所属公司：<?= $administrator->company ? $administrator->company->name : '--'; ?></p>
			                <p>所属部门：<?= $administrator->department ? $administrator->department->name : '--'; ?></p>
		                </div>
		                <div class="work-state">
		                	<p><?= $administrator->is_dimission ? '离职' : '在职'; ?></p>
		                </div>
			        </div>

			    </div>
			        </div>
			    </div>
			</div>
		</div>
    </div>
    <div class="ibox">
        <div class="ibox-content">
            <div class="row">
                <div class="col-sm-7"><canvas id="my-opportunity"></canvas></div>
                <div class="col-sm-5"><canvas id="my-customer"></canvas></div>
            </div>
        </div>
    </div>

</div>