<?php

/* @var $this yii\web\View */
use backend\assets\FlotAsset;
use common\models\DailyStatistics;
use common\utils\BC;
use common\utils\Decimal;
use yii\helpers\Json;
use yii\helpers\Url;
use zhuravljov\yii\widgets\DateTimePicker;

/* @var $provider yii\data\ActiveDataProvider */
/* @var $dataProvider */
/* @var $beforeData */

FlotAsset::register($this);
$this->title = '交易分析';
$this->params['breadcrumbs'][] = $this->title;
$actionUniqueId = Yii::$app->controller->action->uniqueId;
$getValue = Yii::$app->request->get();
$starting_time = Yii::$app->request->get('starting_time');
$end_time = Yii::$app->request->get('end_time');
$daily_statistics = new DailyStatistics();
?>
<div class="row">
<div class="col-lg-12">
    <div class="ibox-content">
    <ul class="nav nav-tabs">
        <li<?php if ($actionUniqueId == 'statistics/yesterday' && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
            <a href="<?= Url::to(['yesterday']) ?>">昨天</a>
        </li>
        <li<?php if ($actionUniqueId == 'statistics/this-week' && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
            <a href="<?= Url::to(['this-week']) ?>">本周</a>
        </li>
        <li<?php if ($actionUniqueId == 'statistics/this-month' && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
            <a href="<?= Url::to(['this-month']) ?>">本月</a>
        </li>
        <li<?php if ($actionUniqueId == 'statistics/this-year' && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
            <a href="<?= Url::to(['this-year']) ?>">本年</a>
        </li>
        <li <?php if ($starting_time||$end_time): ?> class="active"<?php endif; ?>>
            <a>自定义时间段</a>
        </li>
        <li>
            <?php
            $labelOptions = ['labelOptions' => ['class' => false]];
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['this-week'],
                'layout' => 'inline',
                'method' => 'get',
            ]); ?>
            <?= $form->field($searchModel, 'starting_time')->widget(DateTimePicker::className(), [
                'clientOptions' => [
                    'format' => 'yyyy-mm-dd',
                    'language' => 'zh-CN',
                    'autoclose' => true,
                    'minView' => 'month',
                ],
                'clientEvents' => [],
            ]) ?>
            <?= $form->field($searchModel, 'end_time')->widget(DateTimePicker::className(), [
                'clientOptions' => [
                    'format' => 'yyyy-mm-dd',
                    'language' => 'zh-CN',
                    'autoclose' => true,
                    'minView' => 'month',
                ],
                'clientEvents' => [],
            ]) ?>
            <button type="submit" class="btn btn-default">搜索</button>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
        </li>
    </ul>
        <br>
            <div class="ibox-title">
                <h4>交易概况</h4>
            </div>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>支付金额</th>
                <th>支付订单数</th>
                <th>续费订单数</th>
                <th>退款金额</th>
                <th>客单价</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php $pay_price = $daily_statistics->calculation($dataProvider,'pay_price');?>
                    <?= Decimal::formatCurrentYuan($pay_price); ?>
                </td>
                <td><?= $pay_success_no = $daily_statistics->calculation($dataProvider,'pay_success_no');
                ?></td>
                <td><?= $refunds_order_no = $daily_statistics->calculation($dataProvider,'renewal_order_no'); ?></td>
                <td><?= $refunds_price = $daily_statistics->calculation($dataProvider,'refunds_price'); ?></td>
                <td>
                    <?php $pay_user_no = $daily_statistics->calculation($beforeData,'pay_user_no'); ?>
                    <?=  $customer_price = BC::div($pay_price,$pay_user_no); ?>
                </td>
            </tr>
            <?php if(empty($getValue)):  ?>
            <tr>
                <td>
                    <?php
                    $before_pay_price = $daily_statistics->calculation($beforeData,'pay_price');
                    if(BC::sub($pay_price,$before_pay_price) > 0):?>
                    <span style="color: green">
                        <?= $daily_statistics->comparison($pay_price,$before_pay_price,2,$actionUniqueId); ?>
                    </span>
                    <?php else: ?>
                        <span style="color: red">
                            <?= $daily_statistics->comparison($pay_price,$before_pay_price,2,$actionUniqueId); ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $before_pay_success_no = $daily_statistics->calculation($beforeData,'pay_success_no');
                    if(BC::sub($pay_success_no,$before_pay_success_no) > 0): ?>
                    <span style="color: green">
                        <?= $daily_statistics->comparison($pay_success_no,$before_pay_success_no,2,$actionUniqueId); ?>
                    </span>
                    <?php else: ?>
                        <span style="color: red">
                            <?= $daily_statistics->comparison($pay_success_no,$before_pay_success_no,2,$actionUniqueId); ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $before_refunds_order_no = $daily_statistics->calculation($beforeData,'renewal_order_no');
                    if(BC::sub($refunds_order_no,$before_refunds_order_no) > 0): ?>
                    <span style="color: green">
                        <?= $daily_statistics->comparison($refunds_order_no,$before_refunds_order_no,2,$actionUniqueId); ?>
                    </span>
                    <?php else: ?>
                        <span style="color: red">
                            <?= $daily_statistics->comparison($refunds_order_no,$before_refunds_order_no,2,$actionUniqueId); ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $before_refunds_price = $daily_statistics->calculation($beforeData,'refunds_price');
                    if(BC::sub($refunds_price,$before_refunds_price) > 0): ?>
                    <span style="color: green">
                        <?= $daily_statistics->comparison($refunds_price,$before_refunds_price,2,$actionUniqueId); ?>
                    </span>
                    <?php else: ?>
                        <span style="color: red">
                            <?= $daily_statistics->comparison($refunds_price,$before_refunds_price,2,$actionUniqueId); ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $before_pay_user_no = $daily_statistics->calculation($beforeData,'pay_user_no');
                    $before_customer_price = $before_pay_price > 0 ? BC::div($before_pay_price,$before_pay_user_no): 0;
                    if(BC::sub($customer_price,$before_customer_price) > 0): ?>
                    <span style="color: green">
                        <?= $daily_statistics->comparison($customer_price,$before_customer_price,2,$actionUniqueId); ?>
                    </span>
                    <?php else: ?>
                        <span style="color: red">
                            <?= $daily_statistics->comparison($customer_price,$before_customer_price,2,$actionUniqueId); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-lg-12">
        <div class="ibox-title">
            <div class="text-center">交易转化漏斗</div>
        </div>
        </div>
        <div class="col-lg-6">
            <div class="ibox-content">
                <ul class="stat-list">
                    <li>
                        <small>浏览网站数 用户数 <?= $visitors_no = $daily_statistics->calculation($dataProvider,'visitors_no'); ?></small>
                        <div class="progress progress-mini">
                            <div style="width: 100%;" class="progress-bar"></div>
                        </div>
                        <small>100%</small>
                    </li>
                    <li>
                        <small>浏览商品 用户数 <?= $browse_no = $daily_statistics->calculation($dataProvider,'browse_no'); ?></small>
                        <div class="stat-percent">

                        </div>
                        <div class="progress progress-mini">
                            <div style="width: <?= $browse = $daily_statistics->getPercentage($browse_no,$visitors_no); ?>;" class="progress-bar"></div>
                        </div>
                        <small><?= $browse; ?></small>
                    </li>
                    <li>
                        <small>加入购物车 用户数 <?= $shopping_cart_user_no = $daily_statistics->calculation($dataProvider,'shopping_cart_user_no'); ?></small>
                        <div class="progress progress-mini">
                            <div style="width: <?= $shopping_cart_user = $daily_statistics->getPercentage($shopping_cart_user_no,$browse_no); ?>;" class="progress-bar"></div>
                        </div>
                        <small><?= $shopping_cart_user; ?></small>
                    </li>
                    <li>
                        <small>生成订单 用户数 <?= $order_user_no = $daily_statistics->calculation($dataProvider,'order_user_no'); ?></small>
                        <div class="progress progress-mini">
                            <div style="width: <?= $order_user = $daily_statistics->getPercentage($order_user_no,$shopping_cart_user_no); ?>;" class="progress-bar"></div>
                        </div>
                        <small><?= $order_user; ?></small>
                    </li>
                    <li>
                        <small>支付订单 用户数 <?= $pay_user_no = $daily_statistics->calculation($dataProvider,'pay_user_no'); ?></small>
                        <div class="progress progress-mini">
                            <div style="width: <?= $pay_user = $daily_statistics->getPercentage($pay_user_no,$order_user_no); ?>;" class="progress-bar"></div>
                        </div>
                        <small><?= $pay_user; ?></small>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="ibox-content">
                <ul class="stat-list">
                    <li>
                        <small>&nbsp;</small>
                        <div class="progress progress-mini">
                            <div style="width: 100%;" class="progress-bar"></div>
                        </div>
                        <small>100%</small>
                    </li>
                    <li>
                        <small>&nbsp;</small>
                        <div class="progress progress-mini">
                            <div style="width: <?= $browse = $daily_statistics->getPercentage($browse_no,$visitors_no); ?>;" class="progress-bar"></div>
                        </div>
                        <small><?= $browse ?>（商品浏览转化率）</small>
                    </li>
                    <li>
                        <small>&nbsp;</small>
                        <div class="progress progress-mini">
                            <div style="width: <?= $shopping_cart_user = $daily_statistics->getPercentage($shopping_cart_user_no,$visitors_no); ?>;" class="progress-bar"></div>
                        </div>
                        <small><?= $shopping_cart_user ?>（加入购物车转化率）</small>
                    </li>
                    <li>
                        <small>&nbsp;</small>
                        <div class="progress progress-mini">
                            <div style="width: <?= $order_user = $daily_statistics->getPercentage($order_user_no,$visitors_no); ?>;" class="progress-bar"></div>
                        </div>
                        <small><?= $order_user ?>（下单转化率）</small>
                    </li>
                    <li>
                        <small>&nbsp;</small>
                        <div class="progress progress-mini">
                            <div style="width: <?= $pay_user = $daily_statistics->getPercentage($pay_user_no,$visitors_no); ?>;" class="progress-bar"></div>
                        </div>
                        <small><?= $pay_user ?>（支付转化率）</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <?php if (empty($getValue) && $actionUniqueId != 'statistics/yesterday'): ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>交易趋势</h5>
                </div>
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#tab-1">支付金额</a></li>
                        <li class=""><a data-toggle="tab" href="#tab-2">支付订单数</a></li>
                        <li class=""><a data-toggle="tab" href="#tab-3">浏览网站用户数</i></a></li>
                        <li class=""><a data-toggle="tab" href="#tab-4">浏览商品用户数</i></a></li>
                        <li class=""><a data-toggle="tab" href="#tab-5">加入购物车用户数</i></a></li>
                        <li class=""><a data-toggle="tab" href="#tab-6">生成订单用户数</i></a></li>
                        <li class=""><a data-toggle="tab" href="#tab-7">支付订单用户数</i></a></li>
                        <li class=""><a data-toggle="tab" href="#tab-8">客单价</i></a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane active"><!--1-->
                            <div class="panel-body">
                                <canvas id="lineChart"></canvas>
                            </div>
                        </div>
                        <div id="tab-2" class="tab-pane"><!--2-->
                            <div class="panel-body">
                                <canvas id="pay_order_num_chart"></canvas>
                            </div>
                        </div>
                        <div id="tab-3" class="tab-pane"><!--3-->
                            <div class="panel-body">
                                <canvas id="visitors_chart"></canvas>
                            </div>
                        </div>
                        <div id="tab-4" class="tab-pane"><!--4-->
                            <div class="panel-body">
                                <canvas id="browse_chart"></canvas>
                            </div>
                        </div>
                        <div id="tab-5" class="tab-pane"><!--5-->
                            <div class="panel-body">
                                <canvas id="shopping_cart_user_chart"></canvas>
                            </div>
                        </div>
                        <div id="tab-6" class="tab-pane"><!--6-->
                            <div class="panel-body">
                                <canvas id="place_order_chart"></canvas>
                            </div>
                        </div>
                        <div id="tab-7" class="tab-pane"><!--7-->
                            <div class="panel-body">
                                <canvas id="pay_order_user_chart"></canvas>
                            </div>
                        </div>
                        <div id="tab-8" class="tab-pane"><!--8-->
                            <div class="panel-body">
                                <canvas id="sub_order_chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif;
    $order_no = $daily_statistics->calculation($dataProvider,'order_no');
    ?>
    <div class="row">
        <div class="col-lg-3">
            <div class="ibox-title">
                <h4>下单渠道来源</h4>
            </div>
            <div class="ibox-content">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>订单来源</th>
                        <th>数量</th>
                        <th>占比</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>微信</td>
                        <td><?= $vx_order_no = $daily_statistics->calculation($dataProvider,'vx_order_no'); ?></td>
                        <td><?= $daily_statistics->getPercentage($vx_order_no,$order_no) ?></td>
                    </tr>
                    <tr>
                        <td>移动端</td>
                        <td><?= $vx_order_no = $daily_statistics->calculation($dataProvider,'m_order_no'); ?></td>
                        <td><?= $daily_statistics->getPercentage($vx_order_no,$order_no) ?></td>
                    </tr>
                    <tr>
                        <td>PC端</td>
                        <td><?= $vx_order_no = $daily_statistics->calculation($dataProvider,'pc_order_no'); ?></td>
                        <td><?= $daily_statistics->getPercentage($vx_order_no,$order_no) ?></td>
                    </tr>
                    <tr>
                        <td>总计</td>
                        <td><?= $order_no ?></td>
                        <td>
                            <?php if($order_no):  ?>
                                100.00%
                            <?php endif; ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="ibox-title">
            </div>
            <div class="ibox-content">
                <div class="flot-chart-pie-content" id="user-source"></div>
            </div>
        </div>
    </div>
</div>
</div>
<?php if (empty($getValue) && $actionUniqueId != 'statistics/yesterday'): ?>
<?php
if($actionUniqueId == 'statistics/this-year')
{
    $pay_prices = Json::encode($daily_statistics->perMonthData('pay_price'));
    $pay_success_order = Json::encode($daily_statistics->perMonthData('pay_success_no'));
    $visitors_num = Json::encode($daily_statistics->perMonthData('visitors_no'));
    $browse_num = Json::encode($daily_statistics->perMonthData('browse_no'));
    $shopping_cart_user_num = Json::encode($daily_statistics->perMonthData('shopping_cart_user_no'));
    $place_order_num = Json::encode($daily_statistics->perMonthData('order_no'));
    $pay_user_num = Json::encode($daily_statistics->perMonthData('pay_user_no'));
    $date = Json::encode($daily_statistics->getMouth());
}
else
{
    $pay_prices = Json::encode(array_column($dataProvider,'pay_price'));
    $pay_success_order = Json::encode(array_column($dataProvider,'pay_success_no'));
    $visitors_num = Json::encode(array_column($dataProvider,'visitors_no'));
    $browse_num = Json::encode(array_column($dataProvider,'browse_no'));
    $shopping_cart_user_num = Json::encode(array_column($dataProvider,'shopping_cart_user_no'));
    $place_order_num = Json::encode(array_column($dataProvider,'order_no'));
    $pay_user_num = Json::encode(array_column($dataProvider,'pay_user_no'));
    $date = Json::encode($daily_statistics->getTime(array_column($dataProvider,'date')));
}

$this->registerJs(<<<JS

//-------------------支付金额--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$date}'),
        datasets: [
            {
                label: "支付金额",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$pay_prices}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("lineChart").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------支付订单数--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$date}'),
        datasets: [
            {
                label: "订单数",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$pay_success_order}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("pay_order_num_chart").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------浏览网站用户数--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$date}'),
        datasets: [
            {
                label: "浏览网站用户数",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$visitors_num}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("visitors_chart").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------浏览商品用户数--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$date}'),
        datasets: [
            {
                label: "浏览商品用户数",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$browse_num}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("browse_chart").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------加入购物车用户数--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$date}'),
        datasets: [
            {
                label: "浏览商品用户数",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$shopping_cart_user_num}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("shopping_cart_user_chart").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------下单用户数--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$date}'),
        datasets: [
            {
                label: "下单用户数",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$place_order_num}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("place_order_chart").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------支付用户数--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$date}'),
        datasets: [
            {
                label: "支付用户数",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$pay_user_num}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("pay_order_user_chart").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------客单价--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$date}'),
        datasets: [
            {
                label: "客单价",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$place_order_num}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("sub_order_chart").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

JS
);
?>
<?php endif; ?>

<?php
$order_source = $daily_statistics->getOrderSource($daily_statistics->calculation($dataProvider,'vx_order_no'),$daily_statistics->calculation($dataProvider,'m_order_no'),$daily_statistics->calculation($dataProvider,'pc_order_no'));
$this->registerJs(<<<JS

//-------------------用户分析饼图--------------------------
$(function() {
    var data = JSON.parse('{$order_source}');
    var plotObj = $.plot($("#user-source"), data, {
        series: {
            pie: {
                show: true
            }
        },
        grid: {
            hoverable: true
        },
        tooltip: true,
        tooltipOpts: {
            content: "%p.0%, %s", // show percentages, rounding to 2 decimal places
            shifts: {
                x: 20,
                y: 0
            },
            defaultTheme: false
        }
    });
   
});

JS
);
?>
