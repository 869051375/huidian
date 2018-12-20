<?php
/* @var $this yii\web\View */
/* @var $models RefundRecord[] */

use common\models\PayRecord;
use common\models\Property;
use common\models\RefundRecord;
use common\utils\BC;

$this->title = '退款详情';
$this->params['breadcrumbs'] = [
    ['label' => '订单管理', 'url' => ['order-list/all']],
    $this->title
];
$other_refund_time = Property::get('other_refund_time');
$wx_refund_time = Property::get('wx_refund_time');
?>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>退款列表 </h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="footable table table-striped">
                        <thead>
                        <tr>
                            <th>退款单号</th>
                            <th>订单号</th>
                            <th>退款金额</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($models as $model):?>
                            <tr>
                                <td><?= $model->sn;?></td>
                                <td><?= $model->order_sn;?></td>
                                <td><?= $model->refund_amount;?></td>
                                <td>
                                    <?php if($model->status == RefundRecord::STATUS_SUCCESS):?>
                                        <span>已退款</span>
                                    <!--判断是否支付宝下单且距离支付时间超过支付宝限制时间采用线下退款-->
                                    <?php elseif($model->pay_platform == PayRecord::PAY_PLATFORM_ALIPAY && $other_refund_time && (time() > BC::add($model->payRecord->pay_time,BC::mul($other_refund_time,86400,0),0))): ?>
                                        <button class="btn btn-xs btn-primary do-refund-btn" data-target="#confirm-refund-modal"
                                                data-toggle="modal" data-price="<?= $model->refund_amount ?>" data-sn="<?= $model->sn ?>">财务线下退款
                                        </button>
                                        <!--判断是否微信下单且距离支付时间超过微信限制时间采用线下退款-->
                                    <?php elseif($model->pay_platform == PayRecord::PAY_PLATFORM_WX && $wx_refund_time && (time() > BC::add($model->payRecord->pay_time,BC::mul($wx_refund_time,86400,0),0))): ?>
                                        <button class="btn btn-xs btn-primary do-refund-btn" data-target="#confirm-refund-modal"
                                                data-toggle="modal" data-price="<?= $model->refund_amount ?>" data-sn="<?= $model->sn ?>">财务线下退款
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-xs btn-primary confirm-refund-btn" data-target="#confirm-refund-modal"
                                                data-toggle="modal" data-price="<?= $model->refund_amount ?>" data-sn="<?= $model->sn ?>">确认退款
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!--确认退款start-->
    <div class="modal fade" id="confirm-refund-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">确认退款</h4>
                </div>
                <?php
                $model = new \backend\models\ConfirmRefundForm();
                $form = \yii\bootstrap\ActiveForm::begin([
                    'action' => ['refund/do'],
                    'validationUrl' => ['refund/do', 'is_validate' => 1],
                    'enableAjaxValidation' => true,
                    'id' => 'confirm-refund-form',
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-3',
                            'offset' => 'col-sm-offset-3',
                            'wrapper' => 'col-sm-8',
                        ],
                    ],
                ]); ?>
                    <div class="modal-body">
                        <?= $form->field($model, 'password')->passwordInput() ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">确认退款</button>
                        <?= \yii\helpers\Html::activeHiddenInput($model, 'refund_sn', ['id' => 'refund_sn']) ?>
                        <?= \yii\helpers\Html::activeHiddenInput($model, 'refund_price', ['id' => 'refund_price']) ?>
                    </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
            </div>
        </div>
    </div>
    <!--确认退款end-->
</div>
<?php
$doUrl = \yii\helpers\Url::to(['refund/do-refund']);
$validationUrl = \yii\helpers\Url::to(['refund/do-refund', 'is_validate' => 1]);
$this->registerJs(<<<JS
    $('.confirm-refund-btn').click(function(){
        $('#refund_sn').val($(this).attr('data-sn'));
        $('#refund_price').val($(this).attr('data-price'));
    });
    $('.do-refund-btn').click(function(){
        $('#refund_sn').val($(this).attr('data-sn'));
        $('#refund_price').val($(this).attr('data-price'));
        $('#confirm-refund-form').attr('action','{$doUrl}');
        $('#confirm-refund-form').attr('validationUrl','{$validationUrl}');
    });
JS
)?>