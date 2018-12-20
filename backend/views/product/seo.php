<?php

/* @var $this yii\web\View */
/* @var $model \common\models\ProductSeo */
/* @var $product \common\models\Product */

$this->title = 'SEO设置';
if($product->isPackage())
{
    $breadcrumbsUrl = ['product/package-list'];
}
else
{
    $breadcrumbsUrl = ['product/list'];
}
$this->params['breadcrumbs'] = [
    ['label' => '商品管理', 'url' => $breadcrumbsUrl],
    $this->title
];
?>
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <?php if($product->isPackage()):?>
                    <?= $this->render('/product/package-nav-tabs', ['product' => $product]) ?>
                <?php else:?>
                    <?= $this->render('/product/nav-tabs', ['product' => $product]) ?>
                <?php endif;?>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <?php
                        $form = \yii\bootstrap\ActiveForm::begin([
                            'layout' => 'horizontal',
                            'fieldConfig' => [
                                'horizontalCssClasses' => [
                                    'label' => 'col-sm-2',
                                    'offset' => 'col-sm-offset-2',
                                    'wrapper' => 'col-sm-8',
                                ],
                            ],
                        ]); ?>
                        <?= $form->field($model, 'title')->textInput(); ?>
                        <?= $form->field($model, 'keywords')->textInput(); ?>
                        <?= $form->field($model, 'description')->textarea(); ?>
                        <?= \yii\bootstrap\Html::activeHiddenInput($model, 'product_id')?>
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-2">
                                <button type="submit" class="btn btn-primary">保存</button>
                            </div>
                        </div>
                        <?php \yii\bootstrap\ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
