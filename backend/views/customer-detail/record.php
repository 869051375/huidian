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
                    <div class="ibox-title">
                        <h5>历史跟进记录</h5>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-striped m-t-lg">
                            <thead>
                            <tr>
                                <th width="20%">时间</th>
                                <th width="20%">跟进人</th>
                                <th width="60%">跟进内容</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($models as $key => $crmCustomerLog): ?>
                                <tr style="width:100%;">
                                    <td width="20%">
                                        <?= Yii::$app->formatter->asDatetime($crmCustomerLog->created_at) ?>
                                    </td>
                                    <td width="20%"><?= $crmCustomerLog->creator_name ?></td>
                                    <td width="60%"><?= $crmCustomerLog->remark ?></td>
                                </tr>
                            <?php endforeach; ?>
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
        
        $('.record-sure-btn').click(function()
        {
            var form = $('#add-record-form');
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