<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use yii\bootstrap\Html;

/* @var $provider yii\data\ActiveDataProvider */

$this->title = '搜索记录';
$this->params['breadcrumbs'][] = $this->title;
/** @var \common\models\SearchKeywords[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
?>
<div class="row">
    <div class="col-xs-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>搜索记录</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="footable table table-striped">
                        <thead>
                        <tr>
                            <th>时间</th>
                            <th>关键词</th>
                            <th>IP地址</th>
                            <th>结果数</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($models as $model):?>
                            <tr>
                                <td><?= Yii::$app->formatter->asDatetime($model->created_at); ?></td>
                                <td><?= Html::encode($model->keyword); ?></td>
                                <td><?= $model->ip; ?></td>
                                <td><?= $model->result_total; ?></td>
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