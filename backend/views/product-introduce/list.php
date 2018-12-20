<?php

/* @var $this yii\web\View */
use backend\models\DeleteProductImageForm;
use backend\models\ImageForm;
use common\models\ProductImage;
use yii\bootstrap\Html;

/* @var $introduce \common\models\ProductIntroduce */
/* @var $images \common\models\ProductImage[] */
/* @var $product \common\models\Product */

$this->title = '商品图片';
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

$detailImage = null;
$listImage = null;
$carImage = null;
$hotImage = null;
$mobileHotImage = null;
$experienceImage = null;
$mobileDetailImage = null;
foreach ($images as $image) {
    if ($image->type == ProductImage::TYPE_EXPERIENCE) $experienceImage = $image;
    if ($image->type == ProductImage::TYPE_HOT) $hotImage = $image;
    if ($image->type == ProductImage::TYPE_LIST) $listImage = $image;
    if ($image->type == ProductImage::TYPE_DETAIL) $detailImage = $image;
    if ($image->type == ProductImage::TYPE_CAR) $carImage = $image;
    if ($image->type == ProductImage::TYPE_HOT_WAP) $mobileHotImage = $image;
    if ($image->type == ProductImage::TYPE_MOBILE_DETAIL) $mobileDetailImage = $image;
}
?>


    <div class="wrapper wrapper-content animated fadeIn">
        <div class="row">
            <div class="col-xs-12">
                <div class="tabs-container product-images">
                    <?php if($product->isPackage()):?>
                        <?= $this->render('/product/package-nav-tabs', ['product' => $product]) ?>
                    <?php else:?>
                        <?= $this->render('/product/nav-tabs', ['product' => $product]) ?>
                    <?php endif;?>
                    <div class="tab-content">
                        <div class="panel-body" style="border-top: none">
                            <?php
                            $model = new ImageForm();
                            $form = \yii\bootstrap\ActiveForm::begin([
                                'action' => ['product-introduce/update', 'product_id' => $introduce->product_id],
                                'layout' => 'horizontal',
                                'fieldConfig' => [
                                    'horizontalCssClasses' => [
                                        'label' => 'col-sm-2',
                                        'offset' => 'col-sm-offset-2',
                                        'wrapper' => 'col-sm-8',
                                        'hint' => 'col-sm-offset-2 col-sm-8'
                                    ],
                                ],
                            ]); ?>

                            <?php $field = $form->field($model, 'image')->hiddenInput(['id' => 'product-image_image_key'])->label('PC商品详情页图片');
                            $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                                    'buttonTitle' => '上传图片',
                                    'name' => 'file',
                                    'serverUrl' => ['product-introduce/upload'],
                                    'formData' => [
                                        'type' => ProductImage::TYPE_DETAIL,
                                        'product_id' => $introduce->product_id,
                                        Yii::$app->request->csrfParam => Yii::$app->request->csrfToken
                                    ],
                                    'done' => new \yii\web\JsExpression('function (e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-banner_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#detail-image .delete-image").attr("data-id", file.id).show();
                                                $("#detail-image .image").empty().append($("<img class=\"thumbnail margin0\" />").attr("src", file.thumbnailUrl));
                                                $("#product-image_image_key").val(file.key);
                                                $("#product-image_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }')
                                ])
                            ?>
                            <?= $field ?>
                            <div class="row m-b-md">
                                <div class="col-sm-8 col-sm-offset-2">
                                    <div id="detail-image">

                                        <div class="image">
                                            <?php if ($detailImage): ?>
                                                <img class="thumbnail margin0"
                                                     src="<?= $detailImage->getImageUrl(300, 300, 1) ?>"/>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn btn-xs btn-danger delete-image"<?php if(!$detailImage): ?> style="display: none"<?php endif; ?>
                                                data-target="#delete-image-modal"
                                                data-toggle="modal" data-id="<?= $detailImage ? $detailImage->id : ''; ?>"
                                                type="button">删除
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <?php $field = $form->field($model, 'image')->hiddenInput(['id' => 'product-image_image_key'])->label('移动端商品详情页图片');
                            $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                                    'buttonTitle' => '上传图片',
                                    'name' => 'file',
                                    'serverUrl' => ['product-introduce/upload'],
                                    'formData' => [
                                        'type' => ProductImage::TYPE_MOBILE_DETAIL,
                                        'product_id' => $introduce->product_id,
                                        Yii::$app->request->csrfParam => Yii::$app->request->csrfToken
                                    ],
                                    'done' => new \yii\web\JsExpression('function (e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-banner_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#m-detail-image .delete-image").attr("data-id", file.id).show();
                                                $("#m-detail-image .image").empty().append($("<img class=\"thumbnail margin0\" />").attr("src", file.thumbnailUrl));
                                                $("#product-image_image_key").val(file.key);
                                                $("#product-image_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }')
                                ])
                            ?>
                            <?= $field ?>
                            <div class="row m-b-md">
                                <div class="col-sm-8 col-sm-offset-2">
                                    <div id="m-detail-image">

                                        <div class="image">
                                            <?php if ($mobileDetailImage): ?>
                                                <img class="thumbnail margin0"
                                                     src="<?= $mobileDetailImage->getImageUrl(300, 300, 1) ?>"/>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn btn-xs btn-danger delete-image"<?php if(!$mobileDetailImage): ?> style="display: none"<?php endif; ?>
                                                data-target="#delete-image-modal"
                                                data-toggle="modal" data-id="<?= $mobileDetailImage ? $mobileDetailImage->id : ''; ?>"
                                                type="button">删除
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <?php $field = $form->field($model, 'image')->hiddenInput(['id' => 'product-image_image_key'])->label('商品列表页图片')->hint('商品列表页图片要求，宽高：130px &times; 90px，格式：JPG');
                            $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                                    'buttonTitle' => '上传图片',
                                    'name' => 'file',
                                    'serverUrl' => ['product-introduce/upload'],
                                    'formData' => [
                                        'type' => ProductImage::TYPE_LIST,
                                        'product_id' => $introduce->product_id,
                                        Yii::$app->request->csrfParam => Yii::$app->request->csrfToken
                                    ],
                                    'done' => new \yii\web\JsExpression('function (e, data) {
                                            $.each(data.result.files, function (index, file) {                            
                                            if(file.error)
                                            {
                                                $(".field-banner_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {   
                                                $("#list-image .delete-image").attr("data-id", file.id).show();
                                                $("#list-image .image").empty().append($("<img class=\"thumbnail margin0\" />").attr("src", file.thumbnailUrl));
                                                $("#product-image_image_key").val(file.key);
                                                $("#product-image_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }')
                                ])
                            ?>
                            <?= $field ?>
                            <div class="row m-b-md">
                                <div class="col-sm-8 col-sm-offset-2">
                                    <div id="list-image">

                                        <div class="image">
                                            <?php if ($listImage): ?>
                                                <img class="thumbnail margin0"
                                                     src="<?= $listImage->getImageUrl(300, 300, 1) ?>"/>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn btn-xs btn-danger delete-image"<?php if(!$listImage): ?> style="display: none"<?php endif; ?>
                                                data-target="#delete-image-modal"
                                                data-toggle="modal" data-id="<?= $listImage ? $listImage->id : ''; ?>" type="button">删除
                                        </button>

                                    </div>
                                </div>
                            </div>

                            <?php $field = $form->field($model, 'image')->hiddenInput(['id' => 'product-image_image_key'])->label('购物车页面图片')->hint('购物车页图片要求，宽高：52px &times; 42px，格式：JPG');
                            $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                                    'buttonTitle' => '上传图片',
                                    'name' => 'file',
                                    'serverUrl' => ['product-introduce/upload'],
                                    'formData' => [
                                        'type' => ProductImage::TYPE_CAR,
                                        'product_id' => $introduce->product_id,
                                        Yii::$app->request->csrfParam => Yii::$app->request->csrfToken
                                    ],
                                    'done' => new \yii\web\JsExpression('function (e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-banner_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#shopping-cart-image .delete-image").attr("data-id", file.id).show();
                                                $("#shopping-cart-image .image").empty().append($("<img class=\"thumbnail margin0\" />").attr("src", file.thumbnailUrl));
                                                $("#product-image_image_key").val(file.key);
                                                $("#product-image_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }')
                                ])
                            ?>
                            <?= $field ?>
                            <div class="row m-b-md">
                                <div class="col-sm-8 col-sm-offset-2">
                                    <div id="shopping-cart-image">
                                        <div class="image">
                                            <?php if ($carImage): ?>
                                                <img class="thumbnail margin0"
                                                     src="<?= $carImage->getImageUrl(300, 300, 1) ?>"/>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn btn-xs btn-danger delete-image"<?php if(!$carImage): ?> style="display: none"<?php endif; ?>
                                                data-target="#delete-image-modal"
                                                data-toggle="modal" data-id="<?= $carImage ? $carImage->id : ''; ?>" type="button">删除
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <?php $field = $form->field($model, 'image')->hiddenInput(['id' => 'product-image_image_key'])->label('热门商品图片')->hint('热门图片要求，宽高：180px &times; 128px，格式：JPG');
                            $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                                    'buttonTitle' => '上传图片',
                                    'name' => 'file',
                                    'serverUrl' => ['product-introduce/upload'],
                                    'formData' => [
                                        'type' => ProductImage::TYPE_HOT,
                                        'product_id' => $introduce->product_id,
                                        Yii::$app->request->csrfParam => Yii::$app->request->csrfToken
                                    ],
                                    'done' => new \yii\web\JsExpression('function (e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-banner_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#hot-image .delete-image").attr("data-id", file.id).show();
                                                $("#hot-image .image").empty().append($("<img class=\"thumbnail margin0\" />").attr("src", file.thumbnailUrl));
                                                $("#product-image_image_key").val(file.key);
                                                $("#product-image_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }')
                                ])
                            ?>
                            <?= $field ?>
                            <div class="row m-b-md">
                                <div class="col-sm-8 col-sm-offset-2">
                                    <div id="hot-image">
                                        <div class="image">
                                            <?php if ($hotImage): ?>
                                                <img class="thumbnail margin0"
                                                     src="<?= $hotImage->getImageUrl(300, 300, 1) ?>"/>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn btn-xs btn-danger delete-image"<?php if(!$hotImage): ?> style="display: none"<?php endif; ?>
                                                data-target="#delete-image-modal"
                                                data-toggle="modal" data-id="<?= $hotImage ? $hotImage->id : ''; ?>" type="button">删除
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <?php $field = $form->field($model, 'image')->hiddenInput()->label('移动端热门商品图片');
                            $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                                    'buttonTitle' => '上传图片',
                                    'name' => 'file',
                                    'serverUrl' => ['product-introduce/upload'],
                                    'formData' => [
                                        'type' => ProductImage::TYPE_HOT_WAP,
                                        'product_id' => $introduce->product_id,
                                        Yii::$app->request->csrfParam => Yii::$app->request->csrfToken
                                    ],
                                    'done' => new \yii\web\JsExpression('function (e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-banner_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#mobile-hot-image .delete-image").attr("data-id", file.id).show();
                                                $("#mobile-hot-image .image").empty().append($("<img class=\"thumbnail margin0\" />").attr("src", file.thumbnailUrl));                                            
                                            }
                                            });
                                        }')
                                ])
                            ?>
                            <?= $field ?>
                            <div class="row m-b-md">
                                <div class="col-sm-8 col-sm-offset-2">
                                    <div id="mobile-hot-image">
                                        <div class="image">
                                            <?php if ($mobileHotImage): ?>
                                                <img class="thumbnail margin0"
                                                     src="<?= $mobileHotImage->getImageUrl(300, 300, 1) ?>"/>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn btn-xs btn-danger delete-image"<?php if(!$mobileHotImage): ?> style="display: none"<?php endif; ?>
                                                data-target="#delete-image-modal"
                                                data-toggle="modal" data-id="<?= $mobileHotImage ? $mobileHotImage->id : ''; ?>" type="button">删除
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <?= Html::activeHiddenInput($introduce, 'product_id') ?>
                            <?php \yii\bootstrap\ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="delete-image-modal" role="dialog" aria-labelledby="modal-title">
        <?php
        $formModel = new DeleteProductImageForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['product-image/delete'],
            'id' => 'delete-image-form',
        ]); ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">删除图片</h4>
                </div>

                <div class="modal-body">
                    <p>确定删除该图片吗？</p>
                    <?= Html::activeHiddenInput($formModel, 'image_id'); ?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger">删除</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>
<?php $this->registerJs(<<<JS
    $('.product-images').on('click', '.delete-image', function(){
        var id = $(this).attr('data-id');
        $('#deleteproductimageform-image_id').val(id);
        $('#delete-image-form').find('.warning-active').text('');
    });
    $('#delete-image-form').on('beforeSubmit', function(){
        var form = $(this);
        $.post(form.attr('action'), form.serialize(), function(rs){
            if(rs.status === 200)
            {
                form.trigger('reset.yiiActiveForm');
                window.location.reload();
            }
            else
            {
                form.find('.warning-active').text(rs.message);
            }
        }, 'json');
        return false;
    });
JS
); ?>