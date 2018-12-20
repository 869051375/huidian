
<?php
/* @var $this yii\web\View */

$this->title = '编辑个人信息';

$this->params['breadcrumbs'] = [$this->title];

/** @var \common\models\Administrator $model */
?>

<div class="row">
    <div class="col-lg-12">

        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>编辑个人信息</h5>
            </div>
            <div class="ibox-content">
                <?php $form = \yii\bootstrap\ActiveForm::begin([
                     'action' => '',
                    'id' => 'administrator-form',
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-2',
                            'offset' => 'col-sm-offset-2',
                            'wrapper' => 'col-sm-8',
                        ],
                    ],
                ]); ?>
                <?php if($model->type == \common\models\Administrator::TYPE_SALESMAN): ?>
                    <?= $form->field($model, 'name')->staticControl() ?>
                <?php else: ?>
                    <?= $form->field($model, 'name')->textInput() ?>
                <?php endif; ?>
                <div class="hr-line-dashed"></div>
                <?= $form->field($model, 'phone')->textInput() ?>
                <div class="hr-line-dashed"></div>
                <?= $form->field($model, 'email')->textInput() ?>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-2">
                        <button class="btn btn-primary" type="submit">保存</button>
                    </div>
                </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
            </div>
        </div>

    </div>
</div>