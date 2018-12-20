<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/11/6
 * Time: 上午10:24
 */

use common\models\MonthProfitRecord;
use yii\bootstrap\Html;

/** @var array $models */
/** @var \common\models\MonthProfitRecord $record */
$this->title = '预计利润表';
$this->params['breadcrumbs'] = [$this->title];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5><?= $this->title ?></h5>
                <div class="ibox-tools">
                    <?php if (Yii::$app->user->can('expected-profit-settlement/*')): ?>
                        <a href="<?= \yii\helpers\Url::to(['expected-profit-settlement/index']) ?>" class="btn btn-primary btn-sm"><span
                                    class="fa fa-calendar"></span> 预计利润总结算</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ibox-content page-select2-area">
                <ul class="nav nav-tabs">
                    <li<?php if (Yii::$app->controller->action->id == 'index'): ?> class="active"<?php endif; ?>>
                        <a href="<?= \yii\helpers\Url::to(['index']) ?>">个人利润表</a>
                    </li>

                    <li<?php if (Yii::$app->controller->action->id == 'team'): ?> class="active"<?php endif; ?>>
                        <a href="<?= \yii\helpers\Url::to(['team']) ?>">团队利润表</a>
                    </li>

                    <li<?php if (Yii::$app->controller->action->id == 'department'): ?> class="active"<?php endif; ?>>
                        <a href="<?= \yii\helpers\Url::to(['department']) ?>">部门利润表</a>
                    </li>
                </ul>

                <?= Html::beginForm(['team'], 'get', ['role' => 'form', 'class' => 'form-inline']) ?>
                <div class="m-t-md">
                    <div class="form-group">
                        <b>选择时间</b>
                        <?= Html::dropDownList('id', $record ? $record->id : 0, MonthProfitRecord::getAllFinishMonth(), ['class' => 'form-control']) ?>
                    </div>
                    <button type="submit" class="btn btn-default">搜索</button>
                </div>
                <?= Html::endForm(); ?>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>排名</th>
                            <th>部门</th>
                            <th>订单总额（元）</th>
                            <th>订单数量</th>
                            <th>总客户数</th>
                            <th>新客户数</th>
                            <th>更正前预计总利润（元）</th>
                            <th>预计总利润（元）</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sumOrderAmount = 0;
                        $sumOrderCount = 0;
                        $sumExpectedProfit = 0;
                        $sumFront = 0;
                        foreach($models as $i => $model):
                            $sumOrderAmount = \common\utils\BC::add($sumOrderAmount, $model['order_amount']);
                            $sumOrderCount = \common\utils\BC::add($sumOrderCount, $model['order_count'],0);
                            $sumExpectedProfit = \common\utils\BC::add($sumExpectedProfit, $model['expected_profit']);
                            $sumFront = \common\utils\BC::add($sumFront, $model['correct_front_expected_amount']);
                            ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td><?= $model['department_name']; ?></td>
                                <td><?= $model['order_amount']; ?></td>
                                <td><?= $model['order_count']; ?></td>
                                <td><?= $model['customer_count']; ?></td>
                                <td><?= $model['new_customer_count']; ?></td>
                                <td><?= \common\utils\BC::div($model['correct_front_expected_amount'],100,4); ?></td><!--此处数据除以100只做页面展示使用-->
                                <td><?= \common\utils\BC::div($model['expected_profit'],100,4); ?></td><!--此处数据除以100只做页面展示使用-->
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <thead>
                        <tr>
                            <th>总计</th>
                            <th></th>
                            <th><?= $sumOrderAmount; ?></th>
                            <th><?= $sumOrderCount; ?></th>
                            <th></th>
                            <th></th>
                            <th><?= \common\utils\BC::div($sumFront,100,4); ?></th>
                            <th><?= \common\utils\BC::div($sumExpectedProfit,100,4); ?></th><!--此处数据除以100只做页面展示使用-->
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


