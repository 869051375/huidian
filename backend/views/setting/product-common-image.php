<?php

/* @var $this yii\web\View */
/* @var $model \backend\models\SettingProductCommonImageForm */

use backend\models\DeleteSettingImageForm;
use imxiangli\image\storage\ImageStorageInterface;
use imxiangli\upload\JQFileUpLoadWidget;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

$this->title = '默认商品详情页图片';
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


                        <div class="tabs-container">
                            <ul class="nav nav-tabs">
                                <li class="active"><a data-toggle="tab" href="#tab-desktop"><i class="fa fa-desktop"></i></a></li>
                                <li class=""><a data-toggle="tab" href="#tab-mobile"><i class="fa fa-mobile"></i></a></li>
                            </ul>
                            <div class="tab-content">
                                <div id="tab-desktop" class="tab-pane active">
                                    <div class="panel-body">
                                        <?php foreach ($model->attributes as $key => $v): ?>
                                            <?php if(in_array($key, ['product_common_image1', 'product_common_image2', 'product_common_image3', 'product_common_image4', 'product_common_image5'])): ?>
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
                                                                    $("#delete-img-'.$key.'").show();
                                                                    $("#delete-img-'.$key.'").attr("data-file", file.key);
                                                                }
                                                                });
                                                            }')
                                                        ])?>
                                                        <div id="<?= $key ?>-image">
                                                            <?php if($v):?>
                                                                <img class="thumbnail" src="<?= $imageStorage->getImageUrl($v, ['width' => 100, 'height' => 100, 'mode' => 1]) ?>" />
                                                            <?php endif; ?>
                                                        </div>
                                                        <button id="delete-img-<?= $key ?>" class="btn btn-xs btn-danger delete-image"<?php if(!$v): ?> style="display: none"<?php endif; ?>
                                                                data-target="#delete-image-modal"
                                                                data-toggle="modal" data-key="<?= $key ?>" data-file="<?= $v ? $v : '' ?>" type="button">删除图片
                                                        </button>
                                                        <?= Html::activeHiddenInput($model, $key, ['id' => "{$key}-image-url"]) ?>
                                                        <?= Html::error($model, $key) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div id="tab-mobile" class="tab-pane">
                                    <div class="panel-body">
                                        <?php foreach ($model->attributes as $key => $v): ?>
                                            <?php if(in_array($key, ['product_common_m_image1', 'product_common_m_image2', 'product_common_m_image3', 'product_common_m_image4', 'product_common_m_image5'])): ?>
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
                                                                    $("#delete-img-'.$key.'").show();
                                                                    $("#delete-img-'.$key.'").attr("data-file", file.key);
                                                                }
                                                                });
                                                            }')
                                                        ])?>
                                                        <div id="<?= $key ?>-image">
                                                            <?php if($v):?>
                                                                <img class="thumbnail" src="<?= $imageStorage->getImageUrl($v, ['width' => 100, 'height' => 100, 'mode' => 1]) ?>" />
                                                            <?php endif; ?>
                                                        </div>
                                                        <button id="delete-img-<?= $key ?>" class="btn btn-xs btn-danger delete-image"<?php if(!$v): ?> style="display: none"<?php endif; ?>
                                                                data-target="#delete-image-modal"
                                                                data-toggle="modal" data-key="<?= $key ?>" data-file="<?= $v ? $v : '' ?>" type="button">删除图片
                                                        </button>
                                                        <?= Html::activeHiddenInput($model, $key, ['id' => "{$key}-image-url"]) ?>
                                                        <?= Html::error($model, $key) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <?= Html::submitButton('保存', ['class' => 'btn btn-primary btn-block', 'name' => 'provide-button']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-image-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $formModel = new DeleteSettingImageForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['setting/delete-image'],
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
                <?= Html::activeHiddenInput($formModel, 'key'); ?>
                <?= Html::activeHiddenInput($formModel, 'file'); ?>
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

<?php
$this->registerJs(<<<JS
	$('.delete-image').on('click', function(){
        var file = $(this).attr('data-file');
        var key = $(this).attr('data-key');
        $('#deletesettingimageform-file').val(file);
        $('#deletesettingimageform-key').val(key);
        $('#delete-image-form').find('.warning-active').text('');
    });
	$('#delete-image-form').on('beforeSubmit', function(){
        var form = $(this);
        $.post(form.attr('action'), form.serialize(), function(rs){
            if(rs.status === 200)
            {
                form.trigger('reset.yiiActiveForm');
                var key = $('#deletesettingimageform-key').val();
                var file = $('#deletesettingimageform-file').val();
                $('#'+key+'-image').empty();
                $('#delete-image-modal').modal('hide');
                $('#delete-img-'+key).hide();
                $('#'+key+'-image-url').val('');
            }
            else
            {
                form.find('.warning-active').text(rs.message);
            }
        }, 'json');
        return false;
    });
JS
);
?>