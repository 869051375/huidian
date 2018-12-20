<?php
/* @var $dataProvider yii\data\ActiveDataProvider */

use backend\widgets\LinkPager;
/** @var \common\models\OrderBalanceRecord[] $models */
$models = $dataProvider->getModels();
?>
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">申请计算业绩历史记录</h4>
        </div>
        <div class="modal-body input_box">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>操作时间</th>
                    <th>操作状态</th>
                    <th>操作内容</th>
                    <th>操作人</th>
                </tr>
                </thead>
                <tbody id="list-cost">
                <?php foreach ($models as $model):?>
                    <tr>
                        <td><?= date('Y-m-d H:i:s',$model->created_at); ?></td>
                        <td><?= $model->title ?></td>
                        <td><?= $model->content ?></td>
                        <td><?= $model->creator_name ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <?=
            LinkPager::widget([
                'pagination' => $dataProvider->pagination,
            ]);
            ?>
        </div>
    </div>
</div>