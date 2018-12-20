<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;

\backend\assets\LoginAsset::register($this);
?><?php $this->beginPage() ?><!DOCTYPE html>
<html>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body class="gray-bg">
<?php $this->beginBody() ?>
<div class="middle-box loginscreen animated fadeInDown">
    <?= $content ?>
</div>
<?php $this->endBody() ?>
</body>
</html><?php $this->endPage() ?>