<?php

/* @var $this yii\web\View */
/* @var $model \common\models\Featured */

use backend\models\DeleteProductImageForm;
use backend\models\FeaturedImageForm;
use backend\models\ImageForm;
use common\models\Featured;
use common\models\FeaturedItem;
use common\models\ProductImage;
use yii\bootstrap\Html;
use yii\helpers\Url;

$this->title = '添加商品';
$this->params['breadcrumbs'] = [['label' => '推荐位管理', 'url' => ['/featured/list']], $this->title];
$imageStorage = Yii::$app->get('imageStorage');
?>
    <div class="row page-select2-area">
        <div class="col-xs-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>【<?= $model->name ?>】商品列表 </h5>
                    <div class="ibox-tools">
                            <a href="#" class="btn btn-primary btn-sm add-Carousel" data-target="#add_Carousel" data-toggle="modal"><span class="fa fa-plus"></span> 新增</a>
                    </div>
                </div>
                <div class="ibox-content">
                    <ul class="list-group sortablelist">
                        <li class="list-group-item">
                            <div class="row">
                                <span class="col-xs-3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品名称</span>
                                <span class="col-xs-3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;图片</span>
                                <span class="col-xs-2">链接</span>
                                <span class="col-xs-2">排序</span>
                                <span class="col-xs-2">操作</span>
                            </div>
                        </li>
                        <?php foreach($model->allItems as $item):  ?>
                         <li class="list-group-item so1 sortableitem" data-id="<?= $item->id ?>" >
                                <div class="row product-images">
                                    <div class="col-xs-3"><?= $item->getName(); ?></div>
                                    <div class="col-xs-3">
                                        <?php
                                        $image = new FeaturedImageForm();
                                        $form = \yii\bootstrap\ActiveForm::begin([
                                            'action' => ['featured/update', 'featured_item_id' => $item->id],
                                            'layout' => 'horizontal',
                                            'fieldConfig' => [
                                                'horizontalCssClasses' => [
                                                    'label' => 'col-sm-2',
                                                    'offset' => 'col-sm-offset-2',
                                                    'wrapper' => 'col-sm-8',
                                                ],
                                            ],
                                        ]); ?>
                                        <?php $field = $form->field($image, 'image')->hiddenInput(['class' => 'product-image_image_key']);
                                        $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                                                'buttonTitle' => '上传图片',
                                                'name' => 'file',
                                                'serverUrl' => ['featured/upload'],
                                                'formData' => [
                                                    'featured_item_id' => $item->id,
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
                                                $("#delete-img-'.$item->id.'").attr("data-id", file.id).show();
                                                $("#list-image-'.$item->id.' .image").empty().append($("<img class=\"thumbnail margin0\" />").attr("src", file.thumbnailUrl));
                                                $(".product-image_image_key").val(file.key);
                                                $(".product-image_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }')
                                            ])
                                        ?>
                                        <?= $field ?>

                                        <div class="row m-b-md">
                                            <div class="col-sm-8 col-sm-offset-2">
                                                <div id="list-image-<?= $item->id ?>">
                                                    <div class="image">
                                                        <img class="thumbnail margin0" src="<?= $item->getImageUrl(90, 90) ?>"/>
                                                    </div>
                                                    <?php if (Yii::$app->user->can('featured/ajax-create')): ?>
                                                    <button id="delete-img-<?= $item->id ?>" class="btn btn-xs btn-danger delete-image"<?php if(!$item->featuredImage): ?> style="display: none"<?php endif; ?>
                                                            data-target="#delete-image-modal"
                                                            data-toggle="modal" data-id="<?= $item->featuredImage ? $item->featuredImage->id : ''; ?>" type="button">删除图片
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php \yii\bootstrap\ActiveForm::end(); ?>
                                    </div>
                                    <span class="col-xs-2" style="width: 200px;word-wrap:break-word;"><?= $item->link; ?></span>
                                    <div class="col-xs-2">
                                        <span class="btn btn-xs btn-link move-up">
                                                <i class="glyphicon glyphicon-arrow-up"></i>
                                            </span>
                                        <span class="btn btn-xs btn-link move-down">
                                                <i class="glyphicon glyphicon-arrow-down"></i>
                                            </span>
                                    </div>
                                    <div class="col-xs-2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if (Yii::$app->user->can('featured/ajax-delete')): ?>
                                        <span class='btn btn-xs btn-white update-btn' data-target="#add_Carousel" data-toggle="modal"  data-id="<?= $item->id ?>">编辑</span>
                                            <span class='btn btn-xs btn-white del' data-target="#delete_Carousel" data-toggle="modal"  data-id="<?= $item->id ?>">删除</span>
                                        <?php endif; ?>
                                    </div>
                              </div>
                         </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php \backend\assets\SortAsset::register($this); ?>

<?php if (Yii::$app->user->can('featured/ajax-create')): ?>
<div class="modal fade" id="add_Carousel" role="dialog" aria-labelledby="modal-title">
    <?php
    $featuredItem = new FeaturedItem();
    $featuredItem->loadDefaultValues();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['featured/ajax-create'],
        'validationUrl' => ['featured/type-validation'],
        'enableAjaxValidation' => true,
        'id' => 'featured-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-2',
                'offset' => 'col-sm-offset-2',
                'wrapper' => 'col-sm-8',
            ],
        ],
    ]); ?>
    <div class="modal-dialog" role="document" >
        <div class="modal-content" style="width:110%">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">新增推荐位商品</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($featuredItem, 'is_product')->checkbox() ?>
                <?= $form->field($featuredItem, 'name')->textInput() ?>
                <?= $form->field($featuredItem, 'product_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                    'serverUrl' => ['featured/ajax-product-list','id'=>$model->id],
                    'itemsName' => 'products',
                    'nameField' => 'name',
                    'searchKeywordName' => 'keyword',
                ]); ?>
                <?= $form->field($featuredItem, 'move_front_explain')->textarea() ?>
                <?= $form->field($featuredItem, 'move_after_explain')->textarea() ?>
                <?= $form->field($featuredItem, 'link')->textarea() ?>
                <?= \yii\bootstrap\Html::activeHiddenInput($featuredItem, 'featured_id',['id'=>'featured_id', 'value'=>$model->id]) ?>
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
            </div>
        </div>
    </div>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<?php endif; ?>

<?php if (Yii::$app->user->can('featured/ajax-delete')): ?>
<div class="modal fade" id="delete_Carousel" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">删除商品</h4>
            </div>
            <div class="modal-body">
                确定删除吗?
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary sure-btn remove-btn">确定</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<div class="modal fade" id="delete-image-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $formModel = new \backend\models\DeleteFeaturedImageForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['featured/image-delete'],
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


<?php
$sort = Url::to(['featured/sort']);
$ajax_del = Url::to(['ajax-delete']);
$ajax_create = Url::to(['ajax-create']);
$featured_info = Url::to(['featured-product-info']);
$update = Url::to(['update-info', 'id' => '__id__']);
$this->registerJs(<<<JS
        $.fn.select2.defaults.set('width', '100%');
                      
        $('.sortablelist').clickSort({
            speed:200,
            moveCallback: function(source_id, target_id){
            $.post('{$sort}', {source_id: source_id, target_id: target_id}, function(rs){
            }, 'json');
        },
            callback:function(){
                setTimeout(function(){
                    $('.sortableitem').find('.move-up,.move-down').show();
                    var div1 = $('.so1:first');
                    var div2 = $('.so1:last');
                    div1.find('.move-up').hide();
                    div2.find('.move-down').hide();
                }, 30);
            }
        });
        $('.sortablelist').find('.move-up,.move-down').show();
        var div1 = $('.so1:first');
        var div2 = $('.so1:last');
        div1.find('.move-up').hide();
        div2.find('.move-down').hide();
        
		
	$('.del').on('click',function(){
	    var featured_item_id = $(this).attr('data-id');
	    $('.remove-btn').on('click',function(){
	        $.post('{$ajax_del}',{id:featured_item_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	            window.location.reload();
	        }
	    },'json')
	    })
	})
	
	$('.add-Carousel').on('click',function(){
        $('#featured-form').attr('action', '{$ajax_create}');
        $('#featured-form').trigger('reset.yiiActiveForm');
        IsProduct();
    });
	
	
	$('.delete-image').click(function(){
        var id = $(this).attr('data-id');
        $('#deletefeaturedimageform-image_id').val(id);
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
    
    function IsProduct()
    {
        //按类型显示表单
        if($('#featureditem-is_product').is(':checked'))
        {
            $('.field-featureditem-product_id').show();       
            //$('.field-featureditem-name').hide();       
            $('.field-featureditem-link').hide();       
        }
        else
        {
            $('.field-featureditem-product_id').hide();   
            //$('.field-featureditem-name').show();   
            $('.field-featureditem-link').show();   
        }
    }
    
    IsProduct();
    
    $('#featureditem-is_product').click(function() 
    {
        IsProduct();
    });
    
    $('.update-btn').click(function()
    {
        var id = $(this).attr('data-id');
        var updateAction = '{$update}';
        $('#featured-form').attr('action', updateAction.replace('__id__', id));
        $.post('{$featured_info}',{id:id},function(rs)
        {
            if(rs.status === 200)
            {
                setData(rs.model);
                
            }
        },'json')
    });
    
    function setData(model)
    {
        if(model.is_product === 1)
        {      
            //$('.field-featureditem-name').hide();       
            $('.field-featureditem-link').hide(); 
            //$('.field-featureditem-product_id').hide(); 
            var p = $('#featureditem-product_id');
            p.append($('<option value="'+model.product_id+'">'+model.product_name+'</option>'));
            p.val(model.product_id);
            $('#featureditem-is_product').prop('checked', true);
            $('#featureditem-move_front_explain').val(model.move_front_explain);
            $('#featureditem-move_after_explain').val(model.move_after_explain);
            $('#featureditem-name').val(model.name);
            $('#featureditem-link').val(model.link);
        }
        else
        {
            $('.field-featureditem-product_id').hide();   
            //$('.field-featureditem-name').show();   
            $('.field-featureditem-link').show();
            $('#featureditem-is_product').prop('checked', false);
            $('#featureditem-move_front_explain').val(model.move_front_explain);
            $('#featureditem-move_after_explain').val(model.move_after_explain);
            $('#featureditem-name').val(model.name);
            $('#featureditem-link').val(model.link);
        }
    }
JS
    );
?>
