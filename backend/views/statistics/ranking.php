<?php

/* @var $this yii\web\View */
use backend\assets\FlotAsset;
use backend\widgets\LinkPager;
use common\models\DailyStatistics;
use common\models\ProductStatistics;
use common\utils\BC;
use common\utils\Decimal;
use imxiangli\select2\Select2Widget;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use zhuravljov\yii\widgets\DateTimePicker;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \backend\models\SummarySearch */

FlotAsset::register($this);
$this->title = '商品分析';
$this->params['breadcrumbs'][] = $this->title;
$actionUniqueId = Yii::$app->controller->action->uniqueId;
$starting_time = Yii::$app->request->get('starting_time');
$end_time = Yii::$app->request->get('end_time');
$status = Yii::$app->request->get('status');
/** @var ProductStatistics[] $models */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$url = ltrim(Yii::$app->request->url,'/');
$daily_statistics = new DailyStatistics();
//var_dump($url);die;
?>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox-content">
            <ul class="nav nav-tabs">
                <?php if($actionUniqueId == 'statistics/transaction-ranking'):  ?>
                    <li<?php if ($status==1 && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['statistics/transaction-ranking','status'=>1]) ?>">昨天</a>
                    </li>
                    <li<?php if ($status==2 && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['statistics/transaction-ranking','status'=>2]) ?>">本周</a>
                    </li>
                    <li<?php if ($status==3 && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['statistics/transaction-ranking','status'=>3]) ?>">本月</a>
                    </li>
                <?php elseif($actionUniqueId == 'statistics/price-ranking'): ?>
                    <li<?php if ($status==1 && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['statistics/price-ranking','status'=>1]) ?>">昨天</a>
                    </li>
                    <li<?php if ($status==2 && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['statistics/price-ranking','status'=>2]) ?>">本周</a>
                    </li>
                    <li<?php if ($status==3 && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['statistics/price-ranking','status'=>3]) ?>">本月</a>
                    </li>
                <?php elseif($actionUniqueId == 'statistics/visitor-ranking'): ?>
                    <li<?php if ($status==1 && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['statistics/visitor-ranking','status'=>1]) ?>">昨天</a>
                    </li>
                    <li<?php if ($status==2 && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['statistics/visitor-ranking','status'=>2]) ?>">本周</a>
                    </li>
                    <li<?php if ($status==3 && empty($starting_time) && empty($end_time)): ?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['statistics/visitor-ranking','status'=>3]) ?>">本月</a>
                    </li>
                <?php endif; ?>
                <li <?php if ($starting_time||$end_time): ?> class="active"<?php endif; ?>>
                    <a>自定义时间段</a>
                </li>
                <?php
                /**@var $form[]**/
                $categoryUrl = \yii\helpers\Url::to(['product-category/ajax-list', 'parent_id' => '__parent_id__']);
                $productUrl = \yii\helpers\Url::to(['product/ajax-list', 'category_id' => '__category_id__']);

                $cityUrl = \yii\helpers\Url::to(['region/ajax-cities', 'province_id' => '__province_id__']);
                $districtUrl = \yii\helpers\Url::to(['region/ajax-districts', 'city_id' => '__city_id__']);
                $labelOptions = ['labelOptions' => ['class' => false]];
                $form = \yii\bootstrap\ActiveForm::begin([
                    'action' => [$url],
                    'layout' => 'inline',
                    'method' => 'get',
                ]);
                ?>
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
                    <button type="submit" class="btn btn-default">搜索</button>
                </li>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
                <br>
            </ul>
            <br>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="ibox-content">
        <ul class="nav nav-tabs">
            <li<?php if ($actionUniqueId=='statistics/transaction-ranking'): ?> class="active"<?php endif; ?>>
                <a href="<?= Url::to(['statistics/transaction-ranking','status'=>2]) ?>">商品交易数量排行</a>
            </li>
            <li<?php if ($actionUniqueId=='statistics/price-ranking'): ?> class="active"<?php endif; ?>>
                <a href="<?= Url::to(['statistics/price-ranking','status'=>2]) ?>">商品交易金额排行</a>
            </li>
            <li<?php if ($actionUniqueId=='statistics/visitor-ranking'): ?> class="active"<?php endif; ?>>
                <a href="<?= Url::to(['statistics/visitor-ranking','status'=>2]) ?>">商品访客排行</a>
            </li>
        </ul>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="ibox-content">
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
                <?php
                foreach($models as $key => $item): ?>
                    <tr>
                        <td><?= $key+1 ?></td>
                        <td><?= $item->product_name ?></td>
                        <td><?= $item->product_visitors ?></td>
                        <td><?= $item->product_pv ?></td>
                        <td><?= $item->product_order_no ?></td>
                        <td><?= $item->pay_success_no ?></td>
                        <td><?= $daily_statistics->getPercentage($item->product_order_no,$item->product_visitors) ?></td>
                        <td><?= $daily_statistics->getPercentage($item->pay_success_no,$item->product_visitors) ?></td>
                        <td><?= $item->total_amount ?></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-12">
            <div class="margin-auto">
                <?= LinkPager::widget(['pagination' => $pagination]); ?>
            </div>
    </div>
</div>
