<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use common\models\CheckNameRecord;
use yii\bootstrap\Html;

/* @var $provider yii\data\ActiveDataProvider */

$this->title = '核名预查记录';
$this->params['breadcrumbs'][] = $this->title;
/** @var \common\models\CheckNameRecord[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
?>
<div class="row">
    <div class="col-xs-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>核名预查记录</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="footable table table-striped">
                        <thead>
                        <tr>
                            <th>查询时间</th>
                            <th>姓名/昵称</th>
                            <th>手机号</th>
                            <th>核查名称</th>
                            <th>通过率</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($models as $model):?>
                            <tr>
                                <td><?= Yii::$app->formatter->asDatetime($model->created_at); ?></td>
                                <td><?= $model->user ? $model->user->name : '--'; ?></td>
                                <td><?= $model->user ? $model->user->phone : '--'; ?></td>
                                <td><?= Html::encode($model->company_name); ?></td>
                                <td><span class="text-danger"><?= $model->possibility == CheckNameRecord::POSSIBILITY_LOW ? '低' : ($model->possibility == CheckNameRecord::POSSIBILITY_MEDIUM ? '中' : '高')?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="9">
                                <?= LinkPager::widget(['pagination' => $pagination]); ?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>