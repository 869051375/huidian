<?php

/* @var $this yii\web\View */
use backend\models\CrmCustomerForm;
use common\models\CrmCustomer;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var \common\models\BusinessSubject $subject */
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
?>
<div class="row wrapper white-bg" style="position: relative;top: -40px; margin: 0 -35px;padding: 0;">
    <div class="ibox-content" style="padding: 0;">
        <div class="row" style="margin: 0;padding: 15px 10px;border-bottom:1px solid #e7eaec;">
            <div class="col-lg-10">
                <img class="img-circle" src="<?= Yii::$app->urlManager->baseUrl.'/css/img/profile_small.png'?>" alt="<?= empty($subject->subject_type) ? $subject->company_name : $subject->region; ?>" width='62' height="62" style="margin-right: 15px;">
                <span style="font-size: 20px;"><?= empty($subject->subject_type) ? $subject->company_name : $subject->region; ?></span>
            </div>
            <div class="col-lg-2" style="text-align: right;padding-top: 15px;">
                <span>
                </span>
            </div>
        </div>
        <div class="row" style="margin: 0;padding: 0 102px;">
            <table class="table" style="margin-top: 20px;">
                <thead>
                <tr>
                    <th width="20%" style="border: none;">关联客户</th>
                    <th width="20%" style="border: none;">创建时间</th>
                    <th width="20%" style="border: none;">创建人</th>
                    <th width="20%" style="border: none;">最后修改时间</th>
                    <th width="20%" style="border: none;">最后修改人</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="border: none;">
                        <a <?php if (Yii::$app->user->can('customer/*')): ?>href="<?= Url::to(['customer-detail/information','id' => $subject->customer->id]) ?>"<?php endif; ?> target="_blank">
                        <?= $subject->customer->name ?>&nbsp;&nbsp;
                        <?= $subject->customer->phone ?>
                        </a>
                    </td>
                    <td style="border: none;"><?= date('Y-m-d H:i:s',$subject->created_at); ?></td>
                    <td style="border: none;"><?= $subject->creator_name; ?></td>
                    <td style="border: none;"><?= $subject->updated_at ? date('Y-m-d H:i:s',$subject->updated_at) : '--'; ?></td>
                    <td style="border: none;"><?= $subject->updater_name; ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

