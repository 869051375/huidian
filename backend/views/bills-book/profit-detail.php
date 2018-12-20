<?php
/** @var $this \yii\web\View */
/* @var $provider \yii\data\ActiveDataProvider */
/** @var $model \common\models\Administrator */
/** @var $correctPerformance \common\models\PerformanceStatistics[] */
/** @var $orderCalculateCollect \common\models\OrderPerformanceCollect */
/** @var $orderPerformanceCollect \common\models\OrderPerformanceCollect */
/** @var $year string */
/** @var $month string */
/** @var $profitRecord \common\models\MonthProfitRecord */

use backend\widgets\LinkPager;
use common\utils\Decimal;
use yii\helpers\Url;
$uniqueId = Yii::$app->controller->action->uniqueId;
/** @var \common\models\PerformanceStatistics[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
$date = Yii::$app->request->get('date');
?>
<?= $this->render('top',[
        'profitRecord' => $profitRecord,
        'year' => $year,
        'month' => $month,
        'model' => $model,
        'orderCalculateCollect' => $orderCalculateCollect,
        'orderPerformanceCollect' => $orderPerformanceCollect,
]) ?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="tabs-container">
                <div class="row">
                    <div class="col-lg-12">
                        <ul class="nav nav-tabs">
                            <li <?php if($uniqueId == 'bills-book/detail'): ?>class="active"<?php endif;?>>
                                <a href="<?= Url::to(['bills-book/detail','id' => $model->id,'date' => $date]) ?>">预计利润金额流水</a>
                            </li>
                            <li <?php if($uniqueId == 'bills-book/profit-detail'): ?>class="active"<?php endif;?>>
                                <a href="<?= Url::to(['bills-book/profit-detail','id' => $model->id,'date' => $date]) ?>">提成金额流水</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>提成计算历史</b>：</p>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 304px;">计算时间</th>
                                    <th class="text-center" style="width: 265px;">提点类型</th>
                                    <th class="text-center" style="width: 265px;">提点</th>
                                    <th class="text-center" style="width: 265px;">虚拟订单号</th>
                                    <th class="text-center" style="width: 265px;">子订单号</th>
                                    <th class="text-center" style="width: 265px;">已计算实际利润金额</th>
                                    <th class="text-center" style="width: 265px;">提成金额</th>
                                    <th class="text-center" style="width: 224px;">操作人</th>
                                </tr>
                                </thead>
                                <tbody id="cost-record-list">
                                <?php foreach ($models as $performance): ?>
                                    <tr style="background: none;">
                                        <td class="text-center"><?= Yii::$app->formatter->asDatetime($performance->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                        <td class="text-center"><?= $performance->getAlgorithmName(); ?></td>
                                        <td class="text-center"><?= floatval($performance->reward_proportion).'%'; ?></td>
                                        <td class="text-center">
                                            <?php if (Yii::$app->user->can('virtual-order-list/list')): ?>
                                                <a href="<?= Url::to(['virtual-order/order', 'vid' => $performance->order->virtualOrder->id]) ?>" target="_blank"><?= $performance->order->virtualOrder->sn; ?></a>
                                            <?php else: ?>
                                                <?= $performance->order->virtualOrder->sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (Yii::$app->user->can('virtual-order-action/detail') || $performance->order->hasDetail()): ?>
                                                <a href="<?= Url::to(['order/info', 'id' => $performance->order->id]) ?>" target="_blank"><?= $performance->order->sn; ?></a>
                                            <?php else: ?>
                                                <?= $performance->order->sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($performance->calculated_performance); ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($performance->performance_reward); ?></td>
                                        <td class="text-center"><?= $performance->creator_name; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?=
                            LinkPager::widget([
                                'pagination' => $pagination
                            ]);
                            ?>
                        </div>
                        <p class="border-bottom p-sm">
                            <i class="border-left-color m-r-sm"></i><b>业绩提成金额更正计算历史</b>：
                            <?php if(Yii::$app->user->can('virtual-order-action/performance-correct')): ?>
                            <button class="btn btn-primary correct-performance-btn" data-target="#correct-performance" data-toggle="modal">提成金额更正</button>
                            <?php endif; ?>
                        </p>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 304px;">更正时间</th>
                                    <th class="text-center" style="width: 265px;">虚拟订单号</th>
                                    <th class="text-center" style="width: 265px;">子订单号</th>
                                    <th class="text-center" style="width: 265px;">金额名称</th>
                                    <th class="text-center" style="width: 265px;">更正金额</th>
                                    <th class="text-center" style="width: 265px;">提点</th>
                                    <th class="text-center" style="width: 265px;">提成金额</th>
                                    <th class="text-center" style="width: 265px;">更正备注</th>
                                    <th class="text-center" style="width: 224px;">操作人</th>
                                </tr>
                                </thead>
                                <tbody id="cost-record-list">
                                <?php foreach ($correctPerformance as $item): ?>
                                    <tr style="background: none;">
                                        <td class="text-center"><?= Yii::$app->formatter->asDatetime($item->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                        <td class="text-center">
                                            <?php if (Yii::$app->user->can('virtual-order-list/list')): ?>
                                                <a href="<?= Url::to(['virtual-order/order', 'vid' => $item->order->virtual_order_id]) ?>" target="_blank"><?= $item->order->virtualOrder->sn; ?></a>
                                            <?php else: ?>
                                                <?= $item->order->virtualOrder->sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (Yii::$app->user->can('virtual-order-action/detail') || $item->order->hasDetail()): ?>
                                                <a href="<?= Url::to(['order/info', 'id' => $item->order->id]) ?>" target="_blank"><?= $item->order->sn; ?></a>
                                            <?php else: ?>
                                                <?= $item->order->sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= $item->title; ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($item->calculated_performance); ?></td>
                                        <td class="text-center"><?= floatval($item->reward_proportion).'%'; ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($item->performance_reward); ?></td>
                                        <td class="text-center"><?= $item->remark; ?></td>
                                        <td class="text-center"><?= $item->creator_name; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--业绩更正金额开始-->
<div class="modal fade" id="correct-performance" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <?php
    $performanceCorrectForm = new \backend\models\BillsPerformanceCorrectForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['virtual-order-action/bills-performance-correct'],
        'validationUrl' => ['virtual-order-action/bills-performance-correct', 'is_validate' => '1'],
        'enableAjaxValidation' => false,
        'id' => 'performance-correct-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-3',
                'wrapper' => 'col-sm-7',
                'hint' => 'col-sm-2'
            ],
        ],
    ]); ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">订单提成金额更正</h4>
            </div>
            <div class="modal-body">
                <?= $form->field($performanceCorrectForm, 'sn')->textInput(); ?>
                <?= $form->field($performanceCorrectForm, 'correct_price')->textInput(); ?>
                <?= $form->field($performanceCorrectForm, 'rate')->textInput()->hint('%'); ?>
                <?= $form->field($performanceCorrectForm, 'title')->textInput(); ?>
                <?= $form->field($performanceCorrectForm, 'content')->textarea(); ?>
                <?= \yii\helpers\Html::activeHiddenInput($performanceCorrectForm,'administrator_id',['value' => $model->id]) ?>
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary correct-sure-btn">确定</button>
            </div>
        </div>
    </div>
    <?php
    $this->registerJs(<<<JS
    //更正业绩
    $(function() 
    {
        var form = $('#performance-correct-form');
        $('.correct-performance-btn').click(function() 
        {
            form.trigger('reset.yiiActiveForm');
            form.find('.warning-active').empty();
        });
        
        form.on('beforeSubmit', function()
        {
            form.find('.correct-sure-btn').text('计算中...').attr('disabled','disabled');
            $.post(form.attr('action'), form.serialize(), function(rs)
            {
                if(rs.status === 200)
                {
                    form.trigger('reset.yiiActiveForm');
                    window.location.reload();
                }
                else
                {
                    form.find('.warning-active').text(rs.message);
                    form.find('.correct-sure-btn').empty().text('确定').removeAttr('disabled');
                }
            }, 'json');
            return false;
        });
    });
JS
    );
    ?>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<!--业绩更正金额结束-->