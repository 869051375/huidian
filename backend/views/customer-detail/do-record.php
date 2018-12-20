<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use common\models\CrmCustomerLog;
use yii\data\ActiveDataProvider;

/** @var ActiveDataProvider $dataProvider */
/** @var CrmCustomerLog[] $models */
$models = $dataProvider ? $dataProvider->getModels() : [];
?>
<div class="wrapper wrapper-content animated fadeIn">
    <?= $this->render('info', ['customer' => $customer]) ?>
    <div class="row">
    <div class="col-xs-12">
        <div class="tabs-container">
        <?= $this->render('nav-tabs', ['customer' => $customer]) ?>
            <div class="tab-content">
            <div class="panel-body" style="border-top: none">
                <div class="ibox">
                    <div class="ibox-content no-borders" style="padding: 0;">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th class="col-md-4">时间</th>
                                <th class="col-md-4">操作人</th>
                                <th class="col-md-4">操作内容</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(count($models)): ?>
                            <?php foreach($models as $key => $crmCustomerLog): ?>
                                <tr>
                                    <td class="col-md-4">
                                        <?= Yii::$app->formatter->asDatetime($crmCustomerLog->created_at) ?>
                                    </td>
                                    <td class="col-md-4"><?= $crmCustomerLog->creator_name ?></td>
                                    <td class="col-md-4"><?= $crmCustomerLog->remark ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <td class="col-md-4"></td>
                                <td class="col-md-4">暂无数据</td>
                                <td class="col-md-4"></td>
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

<?php $this->registerJs(<<<JS
        $('#customerlogform-remark').blur(function() 
        {
          $('.warning-active').text('');
        })
        
        $.fn.select2.defaults.set('width', '80%');
        $('#add-record-form').on('beforeSubmit', function()
        {
            var form = $(this);
            $.post(form.attr('action'), form.serialize(), function(rs){
                if(rs.status === 200)
                {
                    form.trigger('reset.yiiActiveForm');
                    window.location.reload();
                }
                else
                {
                    form.find('.warning-active').text(rs.message);
                }
            }, 'json');
            return false;
        });
JS
);
?>