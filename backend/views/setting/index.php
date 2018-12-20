<?php

/* @var $this yii\web\View */
/* @var $model \backend\models\SettingForm */

use common\widgets\CKEditorWidget;
use imxiangli\image\storage\ImageStorageInterface;
use imxiangli\upload\JQFileUpLoadWidget;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

$this->title = '设置';
?>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5><?= $this->title ?></h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-lg-8">
                        <?php
                        $form = ActiveForm::begin([
                            'id' => 'form-setting',
                        ]);
                        /** @var ImageStorageInterface $imageStorage */
                        $imageStorage = \Yii::$app->get('imageStorage');
                        ?>
                        <?php foreach ($model->attributes as $key => $v): ?>
                            <?php if(in_array($key, ['default_user_avatar', 'default_customer_service_avatar','logo','bottom_logo','default_supervisor_avatar', 'share_link_image'])): ?>
                                <div class="form-group">
                                    <label class="control-label"><?= Html::activeLabel($model, $key)?></label>
                                    <div>
                                        <?= JQFileUpLoadWidget::widget([
                                            'buttonTitle' => '上传',
                                            'name' => 'image',
                                            'serverUrl' => ['setting/upload-image'],
                                            'formData' =>[
                                                Yii::$app->request->csrfParam => Yii::$app->request->csrfToken,
                                            ],
                                            'done' => new \yii\web\JsExpression('function (e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-banner_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {                    
                                                $("#'.$key.'-image").empty().append($("<img class=\"thumbnail\" />").attr("src", file.thumbnailUrl));
                                                $("#'.$key.'-image-url").val(file.key);
                                                $("#'.$key.'-image-url").trigger("blur");
                                            }
                                            });
                                        }')
                                        ])?>
                                        <div id="<?= $key ?>-image">
                                            <?php if($v):?>
                                            <img class="thumbnail" src="<?= $imageStorage->getImageUrl($v, ['width' => 100, 'height' => 100, 'mode' => 1]) ?>" />
                                            <?php endif; ?>
                                        </div>
                                        <?= Html::activeHiddenInput($model, $key, ['id' => "{$key}-image-url"]) ?>
                                        <?= Html::error($model, $key) ?>
                                    </div>
                                </div>
                            <?php elseif(in_array($key, ['global_js_code_pc', 'global_js_code_m', 'order_file_sms_preview', 'send_invoice_sms_preview',
                                'start_service_sms_preview', 'send_renewal_remind_sms_preview', 'assign_clerk_sms_preview', 'nav_more_links'])): ?>
                                <?= $form->field($model, $key)->textarea() ?>
                            <?php elseif(in_array($key, ['default_product_guarantee', 'product_about_us', 'default_product_guarantee_m', 'product_about_us_m', 'company_intro'])): ?>
                                <?= $form->field($model, $key)->widget(CKEditorWidget::className()); ?>
                            <?php elseif($key == 'profit_rule' && Yii::$app->user->can('profit-rule/profit-rule')): ?>
                                <?= $form->field($model, 'profit_rule')->radioList(['本月决定本月（本月预计利润决定本月所签订单在以后计算实际业绩时所在月的提点）','本月决定下月（本月预计利润决定下月所签订单在以后计算实际业绩时所在月的提点）']); ?>
                            <?php elseif(in_array($key, ['default_customer_principal'])): ?>
                                <?php
                                $admin = null;
                                if(!empty($model->$key)){
                                    $admin = \common\models\Administrator::findOne($model->$key);
                                }?>
                                <?= $form->field($model, $key)->widget(\imxiangli\select2\Select2Widget::className(), [
                                    'serverUrl' => ['administrator/ajax-list', 'type' => \common\models\Administrator::TYPE_SALESMAN],
                                    'itemsName' => 'items',
                                    'nameField' => 'name',
                                    'selectedItem' => $admin ? [$admin->id => $admin->name] : [],
                                    'searchKeywordName' => 'keyword',
                                ]); ?>
                            <?php elseif(in_array($key, ['other_refund_time','wx_refund_time']) && Yii::$app->user->can('setting/other-refund-time')): ?>
                                <?= $form->field($model, $key)->textInput() ?>
                            <?php elseif($key == 'pay_rate' && Yii::$app->user->can('expected-profit-rule/rate')): ?>
                                <?= $form->field($model, 'pay_rate')->textInput(['value' => $v ? $v : 30]) ?>
                            <?php elseif($key !== 'other_refund_time' && $key !== 'wx_refund_time' && $key !== 'pay_rate' && $key !== 'profit_rule'): ?>
                                <?= $form->field($model, $key)->textInput() ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <div class="form-group">
                            <?= Html::submitButton('保存设置', ['class' => 'btn btn-primary btn-block', 'name' => 'provide-button']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->registerJs("$.fn.select2.defaults.set('width', '100%');");?>