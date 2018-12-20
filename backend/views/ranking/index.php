<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use common\models\MonthProfitRecord;
use common\models\User;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\Html;
use yii\helpers\Url;
use zhuravljov\yii\widgets\DateTimePicker;

/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \common\models\MonthPerformanceRank $record */
/** @var array $monthList */
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
                    <?= Html::beginForm(['index'], 'get', ['role' => 'form', 'class' => 'form-inline']) ?>
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
                    <?php if($models): ?>
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th width="5%">排名</th>
                            <th width="20%">姓名</th>
                            <th width="20%">部门</th>
                            <th width="20%">职位</th>
                            <th width="20%">当前业绩（元）</th>
                            <th width="35%">业绩提成金额（元）</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($models as $key => $model): ?>
                            <tr>
                                <td><?= $key+1; ?></td>
                                <td><?= $model->administrator_name; ?></td>
                                <td><?= $model->department_name; ?></td>
                                <td>
                                    <?php if($model->administrator):?>
                                    <?= $model->administrator->title; ?>
                                    <?php endif;?>
                                </td>
                                <td><?= $model->calculated_performance; ?></td>
                                <td><?= $model->performance_reward; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>