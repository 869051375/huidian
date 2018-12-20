<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use common\models\User;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\Html;
use yii\helpers\Url;
use zhuravljov\yii\widgets\DateTimePicker;

/** @var array $monthList */
/** @var \common\models\MonthPerformanceRank $record */
$this->title = '提成龙虎榜';
$this->params['breadcrumbs'][] = $this->title;
/**@var \common\models\MonthPerformanceRank[] $models **/
$actionUniqueId = Yii::$app->controller->action->uniqueId;
?>
    <div class="row">
        <div class="col-xs-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5><?= $this->title ?></h5>
                    <?= $this->render('settlement')  ?>
                </div>
                <div class="ibox-content">
                    <ul class="nav nav-tabs">
                        <li <?php if($actionUniqueId == 'ranking/index'):  ?>class="active"<?php endif; ?>>
                            <a href="<?= Url::to(['ranking/index']) ?>">个人提成榜</a>
                        </li>
                        <li <?php if($actionUniqueId == 'ranking/team'):  ?>class="active"<?php endif; ?>>
                            <a href="<?= Url::to(['ranking/team']) ?>">团队提成榜</a>
                        </li>
                        <li <?php if($actionUniqueId == 'ranking/department'):  ?>class="active"<?php endif; ?>>
                            <a href="<?= Url::to(['ranking/department']) ?>">部门提成榜</a>
                        </li>
                    </ul><br>
                    <?= Html::beginForm(['team'], 'get', ['role' => 'form', 'class' => 'form-inline']) ?>
                    <div class="m-t-md">
                        <div class="form-group">
                            <b>选择时间</b>
                            <?= Html::dropDownList('id', $record ? $record->id : 0, \common\models\MonthProfitRecord::getAllFinishMonth(), ['class' => 'form-control']) ?>
                        </div>
                        <button type="submit" class="btn btn-default">搜索</button>
                    </div>
                    <?= Html::endForm(); ?>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>排名</th>
                            <th>团队</th>
                            <th>当前业绩（元）</th>
                            <th>业绩提成金额（元）</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $calculated_performance = 0;
                        $performance_reward = 0;
                        foreach($models as $i => $model):
                            $calculated_performance = \common\utils\BC::add($calculated_performance, $model['calculated_performance']);
                            $performance_reward = \common\utils\BC::add($performance_reward, $model['performance_reward']);
                            ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td><?= $model['department_name']; ?></td>
                                <td><?= $model['calculated_performance']; ?></td>
                                <td><?= $model['performance_reward']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <thead>
                        <tr>
                            <th>总计</th>
                            <th></th>
                            <th><?= $calculated_performance; ?></th>
                            <th><?= $performance_reward; ?></th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>