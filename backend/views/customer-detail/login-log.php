<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use common\models\CrmCustomerLog;
use yii\data\ActiveDataProvider;

/** @var ActiveDataProvider $dataProvider */
/** @var \common\models\UserLoginLog[] $models */
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
                    <div class="ibox-content">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th width="300">登录时间</th>
                                <th width="100">站点</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($models)): ?>
                            <?php foreach($models as $loginLog): ?>
                                <tr>
                                    <td>
                                        <?= Yii::$app->formatter->asDatetime($loginLog->created_at) ?>
                                    </td>
                                    <td><?= $loginLog->getSite(); ?></td>
                                </tr>
                            <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">暂无数据</td>

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