<?php
/** @var $this \yii\web\View */
/* @var $provider \yii\data\ActiveDataProvider */
/** @var $model \common\models\Administrator */
/** @var $correctProfit \common\models\ExpectedProfitSettlementDetail[] */
/** @var $orderCalculateCollect \common\models\OrderCalculateCollect */
/** @var $orderPerformanceCollect \common\models\OrderPerformanceCollect */
/** @var $year string */
/** @var $month string */
/** @var $profitRecord \common\models\MonthProfitRecord */

use backend\widgets\LinkPager;
use common\models\BusinessSubject;
use common\utils\Decimal;
use yii\helpers\Html;
use yii\helpers\Url;
$uniqueId = Yii::$app->controller->action->uniqueId;
/** @var \common\models\ExpectedProfitSettlementDetail[] $models */
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
                        <p class="border-bottom p-sm"><i class="border-left-color m-r-sm"></i><b>预计利润计算历史</b>：</p>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 304px;">计算时间</th>
                                    <th class="text-center" style="width: 265px;">计算类型</th>
                                    <th class="text-center" style="width: 265px;">虚拟订单号</th>
                                    <th class="text-center" style="width: 265px;">子订单号</th>
                                    <th class="text-center" style="width: 265px;">业务主体名称</th>
                                    <th class="text-center" style="width: 265px;">商品名称</th>
                                    <th class="text-center" style="width: 265px;">财务明细编号</th>
                                    <th class="text-center" style="width: 265px;">计算金额</th>
                                    <th class="text-center" style="width: 224px;">操作人</th>
                                </tr>
                                </thead>
                                <tbody id="cost-record-list">
                                <?php foreach ($models as $detail): ?>
                                    <tr style="background: none;">
                                        <td class="text-center"><?= Yii::$app->formatter->asDatetime($detail->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                        <td class="text-center"><?= $detail->getTypeName(); ?></td>
                                        <td class="text-center">
                                            <?php if (Yii::$app->user->can('virtual-order-list/list')): ?>
                                                <a href="<?= Url::to(['virtual-order/order', 'vid' => $detail->order->virtual_order_id]) ?>" target="_blank"><?= $detail->v_sn; ?></a>
                                            <?php else: ?>
                                                <?= $detail->v_sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (Yii::$app->user->can('virtual-order-action/detail') || $detail->order->hasDetail()): ?>
                                                <a href="<?= Url::to(['order/info', 'id' => $detail->order->id]) ?>" target="_blank"><?= $detail->order->sn; ?></a>
                                            <?php else: ?>
                                                <?= $detail->order->sn; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($detail->order->businessSubject): ?>
                                                <?php if ($detail->order->businessSubject->subject_type == BusinessSubject::SUBJECT_TYPE_DISABLED):?>
                                                    <?= $detail->order->businessSubject->company_name; ?>
                                                <?php else:?>
                                                    <?= $detail->order->businessSubject->region; ?>
                                                <?php endif;?>
                                            <?php else: ?>
                                                --
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $detail->order->product_name; ?></td>
                                        <td><?= $detail->order->financial_code; ?></td>
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($detail->expected_profit); ?></td>
                                        <td class="text-center"><?= $detail->creator_name; ?></td>
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
                            <i class="border-left-color m-r-sm"></i><b>预计利润金额更正计算历史</b>：
                            <?php if(Yii::$app->user->can('virtual-order-action/expected-profit-correct')): ?>
                            <button class="btn btn-primary correct-btn" data-target="#correct-price-modal" data-toggle="modal"  type="button">预计利润金额更正</button>
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
                                    <th class="text-center" style="width: 265px;">更正备注</th>
                                    <th class="text-center" style="width: 224px;">操作人</th>
                                </tr>
                                </thead>
                                <tbody id="cost-record-list">
                                <?php foreach ($correctProfit as $item): ?>
                                    <tr style="background: none;">
                                        <td class="text-center"><?= Yii::$app->formatter->asDatetime($item->created_at, 'yyyy-MM-dd HH:mm') ?></td>
                                        <td class="text-center">
                                            <?php if (Yii::$app->user->can('virtual-order-list/list')): ?>
                                                <a href="<?= Url::to(['virtual-order/order', 'vid' => $item->order->virtual_order_id]) ?>" target="_blank"><?= $item->v_sn; ?></a>
                                            <?php else: ?>
                                                <?= $item->v_sn; ?>
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
                                        <td class="text-center"><?= Decimal::formatYenCurrentNoWrap($item->expected_profit); ?></td>
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
<!--预计利润更正start-->
<div class="modal fade" id="correct-price-modal" role="dialog" aria-labelledby="adjust-order-price-label">
<?php
$correctForm = new \backend\models\BillsExpectedProfitCorrectForm();
$form = \yii\bootstrap\ActiveForm::begin([
    'action' => ['virtual-order-action/bills-expected-profit-correct'],
    'id' => 'expected-profit-correct-form',
    'validationUrl' => ['virtual-order-action/bills-expected-profit-correct', 'is_validate' => 1],
    'enableAjaxValidation' => true,
    'layout' => 'horizontal',
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'label' => 'col-sm-3',
            'wrapper' => 'col-sm-8',
            'hint' => 'col-sm-12 col-sm-offset-3',
        ],
    ],
]);
?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="adjust-order-price-label">订单预计利润金额更正</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($correctForm, 'sn')->textInput(); ?>
                <?= $form->field($correctForm, 'correct_price')->textInput(); ?>
                <?= $form->field($correctForm, 'title')->textInput() ?>
                <?= $form->field($correctForm, 'content')->textarea() ?>
                <?= Html::activeHiddenInput($correctForm,'administrator_id',['value' => $model->id]); ?>
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary sure-btn">确定</button>
            </div>
        </div>
    </div>
<?php
$this->registerJs(<<<JS
    $(function() 
    {
        var form = $('#expected-profit-correct-form');
        $('.correct-btn').click(function() 
        {
            form.trigger('reset.yiiActiveForm');
            form.find('.warning-active').text('');
        });
        form.on('beforeSubmit', function()
        {
            var form = $(this);
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
                }
            }, 'json');
            return false;
        });
    })
JS
) ?>
<?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<!--预计利润更正end-->
