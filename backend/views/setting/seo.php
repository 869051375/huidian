<?php

/* @var $this yii\web\View */
/* @var $model \backend\models\SettingSeoForm */

use common\widgets\CKEditorWidget;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

$this->title = '全局SEO设置';
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
                        ?>
                        <?php foreach ($model->attributes as $key => $v): ?>
                            <?php if(in_array($key, [ 'home_seo_keywords', 'home_seo_description', 'head_meta', 'stats_code', 'stats_code_m'])): ?>
                                <?= $form->field($model, $key)->textarea() ?>
                            <?php elseif(in_array($key, ['default_product_guarantee'])): ?>
                                <?= $form->field($model, $key)->widget(CKEditorWidget::className()); ?>
                            <?php else: ?>
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
