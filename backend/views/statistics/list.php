<?php

/* @var $this yii\web\View */
use backend\assets\FlotAsset;
use common\models\DailyStatistics;
use common\models\Order;
use common\models\ProductStatistics;
use common\models\User;
use common\models\VirtualOrder;
use common\utils\Decimal;
use yii\helpers\Json;
use yii\helpers\Url;

/* @var $provider yii\data\ActiveDataProvider */

FlotAsset::register($this);
$this->title = '统计分析';
$this->params['breadcrumbs'][] = $this->title;
$user = new User();
$data = json::encode($user->getUserSourceNo());
$order = new Order();
$product_statistics = new ProductStatistics();
//访问量
$product_visitors_name = json::encode($product_statistics->visitorNo($limit = 10 ,$status = 1));
$product_visitors = json::encode($product_statistics->visitorNo($limit = 10));
//交易量
$product_name = json::encode($order->tradingVolume($limit = 10 ,$status = 1));
$num = json::encode($order->tradingVolume($limit = 10 ));
?>
<div class="row">
    <div class="col-lg-12">
    <div class="ibox-title">
        <div class="text-center">
            暂时不支持当日数据统计，数据统计截止时间为<?= date('Y-m-d',strtotime('-1 day')) ?>
        </div>
    </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
    <div class="ibox-title">
        <h5>交易统计</h5>
        <div class="text-right">
            <?php if (Yii::$app->user->can('statistics/this-week')): ?>
            <a href="<?= Url::to(['statistics/this-week']) ?>" target="_blank">查看详情</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="ibox-content">
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td>支付金额：<?= Decimal::formatCurrentYuan(VirtualOrder::countPrice('total_amount')); ?></td>
            <td>支付转化率：
                <?php $daily_statistics = new DailyStatistics();  ?>
                <?= $daily_statistics->getPercentage($daily_statistics->sumTotal('pay_user_no'),$daily_statistics->sumTotal('visitors_no')) ?></td>
            <td>下单转化率：<?= $daily_statistics->getPercentage($daily_statistics->sumTotal('order_user_no'),$daily_statistics->sumTotal('visitors_no')) ?></td>
            <td>下单支付转化率：<?= $daily_statistics->getPercentage($daily_statistics->sumTotal('pay_user_no'),$daily_statistics->sumTotal('order_user_no')) ?></td>
        </tr>
        <tr>
            <td>退款金额：<?= Decimal::formatCurrentYuan(VirtualOrder::countRefundAmount()); ?></td>
            <td>退款率：<?= $daily_statistics->getPercentage($daily_statistics->sumTotal('refunds_order_no'),$daily_statistics->sumTotal('pay_success_no')) ?></td>
            <td>二次复购率：<?= $daily_statistics->getPercentage($daily_statistics->sumTotal('twice_no'),$daily_statistics->sumTotal('pay_user_no')) ?></td>
            <td>三次及以上复购率：<?= $daily_statistics->getPercentage($daily_statistics->sumTotal('repeatedly_no'),$daily_statistics->sumTotal('pay_user_no')) ?></td>
        </tr>
        </tbody>
    </table>
    </div>
</div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox-title">
            <h5>商品分析</h5>
            <div class="text-right">
                <?php if (Yii::$app->user->can('statistics/week-summary')): ?>
                <a href="<?= Url::to(['statistics/week-summary']) ?>" target="_blank">查看详情</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
            <div class="ibox-title">
                <div class="text-center">商品交易数量top10</div>
            </div>
            <div class="ibox-content">
                <div>
                    <canvas id="product-top"></canvas>
                </div>
            </div>
        </div>
    <div class="col-lg-6">
        <div class="ibox-title">
            <div class="text-center">商品访客排行top10</div>
        </div>
        <div class="ibox-content">
            <div>
                <canvas id="visitor-top"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="ibox-title">
            <h4>用户分析</h4>
        </div>
        <div class="ibox-content">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>用户来源</th>
                    <th>数量</th>
                    <th>占比</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $user_source = $user->sortUserSource();
                if(is_array($user_source)):
                $user_source_no = array_sum(array_column($user_source, 'data'));
                foreach($user_source as $item):

                    ?>
                    <tr>
                        <td><?= $item['label'] ?></td>
                        <td><?= $item['data'] ?></td>
                        <td><?= $daily_statistics->getPercentage($item['data'],$user_source_no) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <thead>
                <tr>
                    <th>合计</th>
                    <th><?= $user_source_no; ?></th>
                    <th>100.00%</th>
                </tr>
                </thead>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="ibox-title">
        </div>
        <div class="ibox-content" style="height: 400px">
            <div class="flot-chart-pie-content" id="user-source"></div>
        </div>
    </div>
</div>


<?php
$this->registerJs(<<<JS

//-------------------用户分析饼图--------------------------
$(function() {
    var data = JSON.parse('{$data}');
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



//-----------------交易量柱状图------------------------
$(function() {
var barData = {
        labels: JSON.parse('{$product_name}'),
        datasets: [
            {
                label: "商品交易数量",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#1ab394",
                data: JSON.parse('{$num}')
            }
        ]
    };

    var barOptions = {
        responsive: true
    };

    var ctx2 = document.getElementById("product-top").getContext("2d");
    new Chart(ctx2, {type: 'bar', data: barData, options:barOptions});
})

//-----------------访客柱状图------------------------
$(function() {
var barData = {
        labels: JSON.parse('{$product_visitors_name}'),
        datasets: [
            {
                label: "访客数",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#1ab394",
                data: JSON.parse('{$product_visitors}')
            }
        ]
    };

    var barOptions = {
        responsive: true
    };

    var ctx2 = document.getElementById("visitor-top").getContext("2d");
    new Chart(ctx2, {type: 'bar', data: barData, options:barOptions});
})

JS
);
?>
