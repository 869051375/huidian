<?php
/* @var $this yii\web\View */
use common\models\Trademark;

/* @var \common\models\CrmCustomer $customer */

?>
    <div class="wrapper wrapper-content animated fadeIn">
        <?= $this->render('info', ['customer' => $customer]) ?>
        <div class="row">
            <div class="col-xs-12">
                <div class="tabs-container">
                    <?= $this->render('nav-tabs', ['customer' => $customer]) ?>
                    <div class="tab-content">
                        <div class="panel-body" style="border-top: none">

                            <table class="table">
                                <thead>
                                <tr>
                                    <th>商标名称</th>
                                    <th>商标说明</th>
                                    <th>商标类别</th>
                                    <th>商标申请号</th>
                                    <th>商标图样</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                /* @var Trademark[] $models */
                                if(count($models)>0): ?>
                                    <?php foreach($models as $model):
                                        ?>
                                        <tr>
                                            <td><?= $model->name ?></td>
                                            <td><?= $model->description ?></td>
                                            <td><?= $model->category_name ?></td>
                                            <td><?= $model->apply_no ?></td>
                                            <td>
                                                <?php if($model->image): ?>
                                                    <img src="<?= $model->getImageUrl(50,50); ?>" alt="<?= $model->name ?>">
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td>暂无数据</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>