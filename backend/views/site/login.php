<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = '登录';
$this->params['breadcrumbs'][] = $this->title;
?>
<div>
    <div class="text-center">
        <h3 class="logo-name">惠点</h3>
    </div>
    <h3 class="text-center">惠点订餐平台</h3>
    <p class="text-center">请输入您的账号和密码以登录。</p>
    <?php $form = ActiveForm::begin(['id' => 'login-form', 'fieldConfig' => ['enableLabel' => true, 'labelOptions' => ['class' => 'sr-only']]]); ?>
    <?= $form->field($model, 'username')->textInput(['placeholder' => $model->getAttributeLabel('username')]) ?>
    <?= $form->field($model, 'password')->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>
    <div class="form-group">
        <?= Html::activeCheckbox($model, 'rememberMe') ?>
    </div>
    <button type="submit" class="btn btn-primary block full-width m-b" name="login-button">登录</button>

<!--    <div class="text-center">-->
<!--        <a href="#">-->
<!--            <small>忘记密码？</small>-->
<!--        </a>-->
<!--    </div>-->

    <?php ActiveForm::end(); ?>
    <p class="m-t text-center">
        <small>惠点订餐平台 &copy; 2018</small>
    </p>
</div>