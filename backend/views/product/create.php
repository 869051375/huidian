<?php
/* @var $this yii\web\View */

/* @var $model backend\models\ProductForm */
/* @var $industries array */
/* @var $addressList array */
$actionUniqueId = Yii::$app->controller->action->uniqueId;
$name = '';
$nav_name = '';
$label_name = '';
if($actionUniqueId == 'product/package-create')
{
    $name = '新增套餐';
    $nav_name = '套餐基本信息设置';
    $label_name = '套餐商品列表';
    $url = ['package-list'];
    $industries = '';
    $addressList = '';
}
else
{
    $name = '新增商品';
    $nav_name = '商品基本信息设置';
    $label_name = '标准商品列表';
    $url = ['list'];
}
$this->title = $name;
/** @var array $url */
$this->params['breadcrumbs'] = [
    ['label' => $label_name, 'url' => $url],
    $this->title
];
?>
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <ul class="nav nav-tabs">
                    <?php if (Yii::$app->user->can('product/update')): ?>
                        <li class="active">
                            <a href="javascript:void();"><?= $nav_name ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <?=
                        $this->render('_form', [
                            'model' => $model,
                            'product' => null,
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