<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use common\models\CrmCustomerLog;
use yii\data\ActiveDataProvider;

/** @var ActiveDataProvider $dataProvider */
/** @var \common\models\BusinessSubject $subject */
/** @var CrmCustomerLog[] $models */
$models = $dataProvider ? $dataProvider->getModels() : [];
?>
<div class="wrapper wrapper-content animated fadeIn">
    <?= $this->render('info', ['subject' => $subject]) ?>
    <div class="row">
    <div class="col-xs-12">
        <div class="tabs-container">
        <?= $this->render('nav-tabs', ['subject' => $subject]) ?>
            <div class="tab-content">
            <div class="panel-body" style="border-top: none">
                <div class="ibox">
                    <div class="ibox-content" style="border-top: none;padding: 0;">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th width="20%">时间</th>
                                <th width="20%">操作人</th>
                                <th>操作内容</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($models)): ?>
                            <?php foreach($models as $key => $crmCustomerLog): ?>
                                <tr>
                                    <td>
                                        <?= Yii::$app->formatter->asDatetime($crmCustomerLog->created_at) ?>
                                    </td>
                                    <td><?= $crmCustomerLog->creator_name ?></td>
                                    <td><?= $crmCustomerLog->remark ?></td>
                                </tr>
                            <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td></td>
                                    <td>暂无数据</td>
                                    <td></td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if($dataProvider): ?>
                            <?=
                            LinkPager::widget([
                                'pagination' => $dataProvider->pagination,
                            ]);
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>