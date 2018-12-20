
<?php
/* @var $this yii\web\View */

$this->title = '修改密码';

$this->params['breadcrumbs'] = [$this->title];

/** @var \common\models\Administrator $model */
?>

<div class="row">
    <div class="col-lg-12">

        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>修改密码</h5>
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
                <?= $form->field($model, 'password')->passwordInput() ?>
                <div class="hr-line-dashed"></div>
                <?= $form->field($model, 'new_password')->passwordInput() ?>
                <div class="hr-line-dashed"></div>
                <?= $form->field($model, 'second_password')->passwordInput() ?>
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