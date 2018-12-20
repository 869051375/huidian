<?php

/* @var $this yii\web\View */
use backend\assets\FlotAsset;
use common\models\DailyStatistics;
use common\models\ProductStatistics;
use common\utils\BC;
use common\utils\Decimal;
use imxiangli\select2\Select2Widget;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use zhuravljov\yii\widgets\DateTimePicker;

/* @var $searchModel \backend\models\SummarySearch */
/* @var $beforeData[] */
/* @var $productStatisticsData[] */
/* @var $chart_time */
/* @var $chart_total_amount */
/* @var $chart_product_visitors */
/* @var $chart_species */
/* @var $chart_pay_success_no */
/* @var $chart_pay_success */
/* @var $chart_order_rate */
/* @var $chart_pay_rate */
/* @var $pie_chart_data [] */

/* @var $transaction_amount [] */
/* @var $areaData[] */
/* @var $transaction_top[] */
/* @var $visitors_top[] */

FlotAsset::register($this);
$this->title = '商品分析';
$this->params['breadcrumbs'][] = $this->title;
$actionUniqueId = Yii::$app->controller->action->uniqueId;
$getValue = Yii::$app->request->get();
$starting_time = Yii::$app->request->get('starting_time');
$end_time = Yii::$app->request->get('end_time');
$daily_statistics = new DailyStatistics();
$product_statistics = new ProductStatistics();

?>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox-content">
            <ul class="nav nav-tabs">
                <li<?php if ($actionUniqueId == 'statistics/yesterday-summary' && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                    <a href="<?= Url::to(['yesterday-summary']) ?>">昨天</a>
                </li>
                <li<?php if ($actionUniqueId == 'statistics/week-summary' && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                    <a href="<?= Url::to(['week-summary']) ?>">本周</a>
                </li>
                <li<?php if ($actionUniqueId == 'statistics/month-summary' && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                    <a href="<?= Url::to(['month-summary']) ?>">本月</a>
                </li>
                <li class="active">&nbsp;&nbsp;&nbsp;</li>
                <li <?php if ($starting_time||$end_time): ?> class="active"<?php endif; ?>>
                    <a>自定义时间段</a>
                </li>
                <?php
                $categoryUrl = \yii\helpers\Url::to(['product-category/ajax-list', 'parent_id' => '__parent_id__']);
                $productUrl = \yii\helpers\Url::to(['product/ajax-list', 'category_id' => '__category_id__']);

                $cityUrl = \yii\helpers\Url::to(['region/ajax-cities', 'province_id' => '__province_id__']);
                $districtUrl = \yii\helpers\Url::to(['region/ajax-districts', 'city_id' => '__city_id__']);
                $labelOptions = ['labelOptions' => ['class' => false]];
                $form = \yii\bootstrap\ActiveForm::begin([
                    'action' => [$actionUniqueId],
                    'layout' => 'inline',
                    'method' => 'get',
                ]); ?>
                <li>

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
                </li>
                <li>
                    <br>
                    <?= $form->field($searchModel, 'top_category_id', $labelOptions)->widget(Select2Widget::className(), [
                        'serverUrl' => \yii\helpers\Url::to(['product-category/ajax-list']),
                        'itemsName' => 'categories',
                        'selectedItem' => $searchModel->topCategory ? [$searchModel->topCategory->id => $searchModel->topCategory->name] : [],
                        'options' => ['prompt' => '选择类目', 'class' => 'form-control'],
                        'placeholderId' => '0',
                        'placeholder' => '选择类目',
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
                        'eventOpening' => new JsExpression("
                                        var id = $('#category_id').val();
                                        serverUrl = '{$productUrl}'.replace('__category_id__', id ? id : '-1');
                                    ")
                    ]) ?>

                    <?= $form->field($searchModel, 'province_id', $labelOptions)->widget(Select2Widget::className(), [
                        'serverUrl' => \yii\helpers\Url::to(['region/ajax-provinces']),
                        'itemsName' => 'provinces',
                        'selectedItem' => $searchModel->province ? [$searchModel->province->id => $searchModel->province->name] : [],
                        'options' => ['class' => 'form-control', 'prompt' => '请选择省份'],
                        'placeholderId' => '0',
                        'placeholder' => '请选择省份',
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
                        'eventOpening' => new JsExpression("
                                        var id = $('#city_id').val();
                                        serverUrl = '{$districtUrl}'.replace('__city_id__', id ? id : '-1');
                                    ")
                    ]); ?>
                    <button type="submit" class="btn btn-default">搜索</button>
                </li>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
                <br>
            </ul>
            <br>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="ibox-title">
            <h4>交易概况</h4>
        </div>
        <div class="ibox-content">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>支付金额</th>
                    <th>被访问商品种类数</th>
                    <th>支付成功商品种类数</th>
                    <th>支付成功商品数</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php
                        $total_amount = $daily_statistics->calculation($productStatisticsData,'total_amount');?>
                        <?= Decimal::formatCurrentYuan($total_amount); ?>
                    </td>
                    <td><?= $product_visitors = $searchModel->handleData($productStatisticsData,'product_visitors')?></td>
                    <td><?= $pay_success_num = $searchModel->handleData($productStatisticsData,'pay_success_no')?></td>
                    <td><?= $pay_success_no = $daily_statistics->calculation($productStatisticsData,'pay_success_no');?></td>
                </tr>
                <?php if (empty($getValue)): ?>
                <tr>
                    <td>
                        <?php
                        $before_total_amount = $daily_statistics->calculation($beforeData,'total_amount');
                        if(BC::sub($total_amount,$before_total_amount) > 0): ?>
                        <span style="color: green">
                            <?= $product_statistics->comparison($total_amount,$before_total_amount,2,$actionUniqueId); ?>
                        </span>
                        <?php else: ?>
                            <span style="color: red">
                            <?= $product_statistics->comparison($total_amount,$before_total_amount,2,$actionUniqueId); ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $before_product_visitors = $daily_statistics->calculation($beforeData,'product_visitors');
                        if(BC::sub($product_visitors,$before_product_visitors) > 0): ?>
                        <span style="color: green">
                        <?= $product_statistics->comparison($product_visitors,$before_product_visitors,2,$actionUniqueId); ?>
                        </span>
                        <?php else: ?>
                        <span style="color: red">
                            <?= $product_statistics->comparison($product_visitors,$before_product_visitors,2,$actionUniqueId); ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $before_pay_success_num = $daily_statistics->calculation($beforeData,'product_visitors');
                        if(BC::sub($pay_success_num,$before_pay_success_num) > 0): ?>
                            <span style="color: green">
                            <?= $product_statistics->comparison($pay_success_num,$before_pay_success_num,2,$actionUniqueId); ?>
                            </span>
                        <?php else: ?>
                            <span style="color: red">
                            <?= $product_statistics->comparison($pay_success_num,$before_pay_success_num,2,$actionUniqueId); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $before_pay_success_no = $daily_statistics->calculation($beforeData,'pay_success_no');
                        if(BC::sub($pay_success_no,$before_pay_success_no) > 0): ?>
                        <span style="color: green">
                            <?= $product_statistics->comparison($pay_success_no,$before_pay_success_no,2,$actionUniqueId); ?>
                        </span>
                        <?php else: ?>
                            <span style="color: red">
                            <?= $product_statistics->comparison($pay_success_no,$before_pay_success_no,2,$actionUniqueId); ?>
                        </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
            <?php if (empty($getValue) && $actionUniqueId != 'statistics/yesterday-summary'): ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>交易趋势</h5>
                        </div>
                        <div class="tabs-container">
                            <ul class="nav nav-tabs">
                                <li class="active"><a data-toggle="tab" href="#tab-1">支付金额</a></li>
                                <li class=""><a data-toggle="tab" href="#tab-2">商品访客数</a></li>
                                <li class=""><a data-toggle="tab" href="#tab-3">被访问商品种类数</i></a></li>
                                <li class=""><a data-toggle="tab" href="#tab-4">支付成功商品种类数</i></a></li>
                                <li class=""><a data-toggle="tab" href="#tab-5">支付成功商品数</i></a></li>
                                <li class=""><a data-toggle="tab" href="#tab-6">下单转化率</i></a></li>
                                <li class=""><a data-toggle="tab" href="#tab-7">支付转化率</i></a></li>
                                <li class=""><a data-toggle="tab" href="#tab-8">下单支付转化率</i></a></li>
                            </ul>
                            <div class="tab-content">
                                <div id="tab-1" class="tab-pane active"><!--1-->
                                    <div class="panel-body">
                                        <canvas id="lineChart"></canvas>
                                    </div>
                                </div>
                                <div id="tab-2" class="tab-pane"><!--2-->
                                    <div class="panel-body">
                                        <canvas id="chart_visitors"></canvas>
                                    </div>
                                </div>
                                <div id="tab-3" class="tab-pane"><!--3-->
                                    <div class="panel-body">
                                        <canvas id="chart_species"></canvas>
                                    </div>
                                </div>
                                <div id="tab-4" class="tab-pane"><!--4-->
                                    <div class="panel-body">
                                        <canvas id="chart_pay_success_no"></canvas>
                                    </div>
                                </div>
                                <div id="tab-5" class="tab-pane"><!--5-->
                                    <div class="panel-body">
                                        <canvas id="chart_pay_success"></canvas>
                                    </div>
                                </div>
                                <div id="tab-6" class="tab-pane"><!--6-->
                                    <div class="panel-body">
                                        <canvas id="chart_order_rate"></canvas>
                                    </div>
                                </div>
                                <div id="tab-7" class="tab-pane"><!--7-->
                                    <div class="panel-body">
                                        <canvas id="chart_pay_rate"></canvas>
                                    </div>
                                </div>
                                <div id="tab-8" class="tab-pane"><!--8-->
                                    <div class="panel-body">
                                        <canvas id="chart_order_pay_rate"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-lg-3">
                    <div class="ibox-title">
                        <h4>商品其他维度分析</h4>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>订单商品状态</th>
                                <th>交易数量</th>
                                <th>占比</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($pie_chart_data)): ?>
                                <?php foreach($pie_chart_data as $item): ?>
                                    <tr>
                                        <td><?= $item['label'] ?></td>
                                        <td><?= $item['data'] ?></td>
                                        <td><?= $daily_statistics->getPercentage($item['data'],$daily_statistics->calculation($pie_chart_data,'data')) ?></td>
                                    </tr>
                                <?php endforeach;?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <div class="col-lg-9">
                <div class="ibox-title">
                    <div class="text-center">不同状态订单商品及占比</div>
                </div>
                <div class="ibox-content">
                    <div class="flot-chart-pie-content" id="order-status"></div>
                </div>
            </div>
        </div>
            <div class="row">
                <div class="col-lg-3">
                    <div class="ibox-title">
                        <div class="text-center">北京市各区域商品交易数量</div>
                    </div>
                    <div class="ibox-content">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>服务区域</th>
                            <th>交易数量</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($areaData)): ?>
                            <?php foreach($areaData as $item): ?>
                                <tr>
                                    <td><?= $item['district_name'] ?></td>
                                    <td><?= $item['num'] ?></td>
                                </tr>
                            <?php endforeach;?>
                        <?php endif; ?>
                        </tbody>
                        <thead>
                        <tr>
                            <th>总计</th>
                            <th><?= $daily_statistics->calculation($areaData,'num') ?></th>
                        </tr>
                        </thead>
                    </table>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="ibox-title">
                        <div class="text-center"></div>
                    </div>
                    <div class="ibox-content">
                        <div>
                            <canvas id="product-top"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    <div class="ibox-title">
                        <div class="text-center">北京市各区域商品交易金额</div>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>服务区域</th>
                                <th>交易金额</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($transaction_amount)): ?>
                                <?php foreach($transaction_amount as $item): ?>
                                    <tr>
                                        <td><?= $item['district_name'] ?></td>
                                        <td><?= $item['num'] ?></td>
                                    </tr>
                                <?php endforeach;?>
                            <?php endif; ?>
                            </tbody>
                            <thead>
                            <tr>
                                <th>总计</th>
                                <th><?= $daily_statistics->calculation($transaction_amount,'num') ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="ibox-title">
                        <div class="text-center"></div>
                    </div>
                    <div class="ibox-content">
                        <div>
                            <canvas id="product-price-top"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                        <div class="tabs-container">
                            <ul class="nav nav-tabs">
                                <li class="active"><a data-toggle="tab" href="#tab-11">商品交易数量排行top10</a></li>
                                <li class=""><a data-toggle="tab" href="#tab-22">商品访客排行top10</a></li>
                            </ul>
                            <div class="tab-content">
                                <div id="tab-11" class="tab-pane active"><!--1-->
                                    <div class="panel-body">
                                        <?php if(is_array($transaction_top) && !empty($transaction_top)): ?>
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>交易排行</th>
                                                <th>商品名称</th>
                                                <th>访客数</th>
                                                <th>浏览量</th>
                                                <th>下单商品数</th>
                                                <th>支付成功商品数</th>
                                                <th>下单转化率</th>
                                                <th>支付转化率</th>
                                                <th>支付金额</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($transaction_top as $key => $item): ?>
                                                    <tr>
                                                        <td><?= $key+1 ?></td>
                                                        <td><?= $item['product_name'] ?></td>
                                                        <td><?= $item['product_visitors'] ?></td>
                                                        <td><?= $item['product_pv'] ?></td>
                                                        <td><?= $item['product_order_no'] ?></td>
                                                        <td><?= $item['pay_success_no'] ?></td>
                                                        <td><?= $daily_statistics->getPercentage($item['product_order_no'],$item['product_visitors']) ?></td>
                                                        <td><?= $daily_statistics->getPercentage($item['pay_success_no'],$item['product_visitors']) ?></td>
                                                        <td><?= $item['total_amount'] ?></td>
                                                    </tr>
                                                <?php endforeach;?>
                                            </tbody>
                                        </table>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div id="tab-22" class="tab-pane"><!--2-->
                                    <div class="panel-body">
                                        <?php if(is_array($visitors_top) && !empty($visitors_top)): ?>
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>访客排行</th>
                                                <th>商品名称</th>
                                                <th>访客数</th>
                                                <th>浏览量</th>
                                                <th>下单商品数</th>
                                                <th>支付成功商品数</th>
                                                <th>下单转化率</th>
                                                <th>支付转化率</th>
                                                <th>支付金额</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($visitors_top as $key => $item): ?>
                                                    <tr>
                                                        <td><?= $key+1 ?></td>
                                                        <td><?= $item['product_name'] ?></td>
                                                        <td><?= $item['product_visitors'] ?></td>
                                                        <td><?= $item['product_pv'] ?></td>
                                                        <td><?= $item['product_order_no'] ?></td>
                                                        <td><?= $item['pay_success_no'] ?></td>
                                                        <td><?= $daily_statistics->getPercentage($item['product_order_no'],$item['product_visitors']) ?></td>
                                                        <td><?= $daily_statistics->getPercentage($item['pay_success_no'],$item['product_visitors']) ?></td>
                                                        <td><?= $item['total_amount'] ?></td>
                                                    </tr>
                                                <?php endforeach;?>
                                            </tbody>
                                        </table>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
</div>
</div>
<?php if (empty($getValue) && $actionUniqueId != 'statistics/yesterday-summary'): ?>
<?php
$this->registerJs(<<<JS
//-------------------支付金额--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$chart_time}'),
        datasets: [
            {
                label: "支付金额",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$chart_total_amount}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("lineChart").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------商品访客数--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$chart_time}'),
        datasets: [
            {
                label: "访客数",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$chart_product_visitors}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("chart_visitors").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------被访问的商品种类数--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$chart_time}'),
        datasets: [
            {
                label: "访客数",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$chart_species}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("chart_species").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------支付成功的商品种类数--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$chart_time}'),
        datasets: [
            {
                label: "支付成功的商品种类数",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$chart_pay_success_no}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("chart_pay_success_no").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------支付成功的商品数--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$chart_time}'),
        datasets: [
            {
                label: "支付成功的商品数",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$chart_pay_success}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("chart_pay_success").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------下单转化率--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$chart_time}'),
        datasets: [
            {
                label: "下单转化率%",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$chart_order_rate}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("chart_order_rate").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------支付转化率--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$chart_time}'),
        datasets: [
            {
                label: "支付转化率%",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$chart_pay_rate}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("chart_pay_rate").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

//-------------------下单支付转化率--------------------------
$(function() {
var lineData = {
        labels: JSON.parse('{$chart_time}'),
        datasets: [
            {
                label: "下单支付转化率%",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: JSON.parse('{$chart_order_pay_rate}')
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };
    var ctx = document.getElementById("chart_order_pay_rate").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});
})

JS
);
?>
<?php endif; ?>
<?php
$district_name = Json::encode(array_column($areaData,'district_name'));
$product_order_num = Json::encode(array_column($areaData,'num'));

$transaction_district_name = Json::encode(array_column($transaction_amount,'district_name'));
$transaction_order_num = Json::encode(array_column($transaction_amount,'num'));

$pie_chart_data = Json::encode($pie_chart_data);
$this->registerJs(<<<JS
//-------------------用户分析饼图--------------------------
$(function() {
    var data = JSON.parse('{$pie_chart_data}');
    var plotObj = $.plot($("#order-status"), data, {
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

//-----------------商品交易数量柱状图------------------------
$(function() {
var barData = {
        labels: JSON.parse('{$district_name}'),
        datasets: [
            {
                label: "商品交易数量",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#1ab394",
                data: JSON.parse('{$product_order_num}')
            }
        ]
    };

    var barOptions = {
        responsive: true
    };

    var ctx2 = document.getElementById("product-top").getContext("2d");
    new Chart(ctx2, {type: 'bar', data: barData, options:barOptions});
})

//-----------------商品交易金额柱状图------------------------
$(function() {
var barData = {
        labels: JSON.parse('{$transaction_district_name}'),
        datasets: [
            {
                label: "商品交易金额",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#1ab394",
                data: JSON.parse('{$transaction_order_num}')
            }
        ]
    };

    var barOptions = {
        responsive: true
    };

    var ctx2 = document.getElementById("product-price-top").getContext("2d");
    new Chart(ctx2, {type: 'bar', data: barData, options:barOptions});
})

JS
);
?>
