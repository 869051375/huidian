<?php
/* @var $this yii\web\View */
/* @var $product common\models\Product */
/* @var $model backend\models\ProductForm */
/* @var $industries array*/
/* @var $addressList array*/

$actionUniqueId = Yii::$app->controller->action->uniqueId;
$titleName = '';
$breadcrumbsUrl = '';
$labelName = '';
if($actionUniqueId == 'product/package-update')
{
    $industries = '';
    $addressList = '';
    $titleName = '编辑套餐';
    $labelName = '套餐商品列表';
    $breadcrumbsUrl = ['package-list'];
}
else
{
    $titleName = '编辑商品';
    $labelName = '标准商品列表';
    $breadcrumbsUrl = ['list'];
}
$this->title = $titleName;
$this->params['breadcrumbs'] = [
    ['label' => $labelName, 'url' => $breadcrumbsUrl],
    $this->title
];

/** @var \yii\data\DataProviderInterface $provider */
/** @var \common\models\Administrator[] $models */
/*$models = $provider->getModels();
$pagination = $provider->getPagination();*/
?>
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <?php if($actionUniqueId == 'product/update'):?>
                    <?= $this->render('nav-tabs', ['product' => $product]) ?>
                <?php else:?>
                    <?= $this->render('package-nav-tabs', ['product' => $product]) ?>
                <?php endif;?>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <?=
                        $this->render('_form', [
                            'model' => $model,
                            'product' => $product,
                            'industries' => $industries,
                            'addressList' => $addressList,
                        ])
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
