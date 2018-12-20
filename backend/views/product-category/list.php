<?php
/** @var \yii\web\View $this */
use imxiangli\select2\Select2Widget;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use common\widgets\CKEditorWidget;

/** @var \common\models\ProductCategory[] $categories */
/** @var int $id */
$this->title = '商品分类管理';

$this->params['breadcrumbs'] = [$this->title];
?>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-xs-5">
                            <strong> 一级分类</strong>
                        </div>
                        <div class="col-xs-5 col-xs-offset-1">
                            <strong>二级分类</strong>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-xs-5">
                            <ul class="list-group sortablelist">
                                <?php
                                $currentParent = null;
                                $children = [];
                                foreach ($categories as $category):
                                    if (empty($currentParent) && ($id == 0 || $id == $category->id)) {
                                        $currentParent = $category;
                                        $children = $category->children;
                                    }
                                    ?>
                                    <li class="list-group-item sortableitem so1 <?= $currentParent && $currentParent->id == $category->id ? 'list-group-item-info' : '' ?>"
                                        data-sort="<?= $category->sort ?>" data-id="<?= $category->id ?>">
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <span class="text-muted">(#<?= $category->id ?>)</span>
                                                <a class="color_66"
                                                   href="<?= \yii\helpers\Url::to(['list', 'id' => $category->id]) ?>"><?= $category->name ?></a>
                                            </div>
                                            <div class="col-xs-6">
                                                 <?php if (Yii::$app->user->can('product-category/seo')): ?>
                                                    <a class="btn btn-xs btn-white pull-right seo-btn"
                                                       data-toggle="modal" data-target="#myModal3" href="#"
                                                       data-whatever="一级分类seo设置">seo设置</a>
                                                <?php endif; ?>            
                                                                                   
                                                <?php if (Yii::$app->user->can('product-category/update')): ?>
                                                    <a class="btn btn-xs btn-white pull-right update-btn"
                                                       data-toggle="modal" data-target="#myModal" href="#"
                                                       data-whatever="编辑一级分类">编辑</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('product-category/delete')): ?>
                                                    <a class="btn btn-xs btn-white pull-right delete-btn"
                                                       data-toggle="modal" data-target="#myModal2" href="#">删除</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('product-category/update')): ?>
                                                    <span class="btn btn-xs btn-link move-up"><i
                                                                class="glyphicon glyphicon-arrow-up"></i></span>
                                                    <span class="btn btn-xs btn-link move-down"><i
                                                                class="glyphicon glyphicon-arrow-down"></i></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (Yii::$app->user->can('product-category/create')): ?>
                                <span class="btn btn-default btn-block add-btn" data-toggle="modal"
                                      data-target="#myModal"
                                      data-whatever="新增一级分类" data-pid="0">新增</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-xs-1">
                            <span class="glyphicon glyphicon-chevron-right text-center center-block"
                                  style="font-size: 24px;color: #1ab394;margin-top: 60px;"></span>
                        </div>
                        <div class="col-xs-5">
                            <ul class="list-group sortablelist">
                                <?php foreach ($children as $category): ?>
                                    <li class="list-group-item hover_li sortableitem so2"
                                        data-sort="<?= $category->sort ?>" data-id="<?= $category->id ?>">
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <span class="text-muted">(#<?= $category->id ?>)</span>
                                                <a class="color_66" href="#"><?= $category->name ?></a>
                                            </div>
                                            <div class="col-xs-6">
                                                 <?php if (Yii::$app->user->can('product-category/seo')): ?>
                                                    <a class="btn btn-xs btn-white pull-right seo-btn"
                                                       data-toggle="modal" data-target="#myModal3" href="#"
                                                       data-whatever="二级分类seo设置">seo设置</a>
                                                 <?php endif; ?>                                                      
                                                <?php if (Yii::$app->user->can('product-category/update')): ?>
                                                    <a class="btn btn-xs btn-white pull-right update-btn"
                                                       data-toggle="modal" data-target="#myModal" data-whatever="编辑二级分类"
                                                       href="#">编辑</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('product-category/delete')): ?>
                                                    <a class="btn btn-xs btn-white pull-right delete-btn" href="#"
                                                       data-toggle="modal"
                                                       data-target="#myModal2">删除</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('product-category/update')): ?>
                                                    <span class="btn btn-xs btn-link move-up">
                                                    <i class="glyphicon glyphicon-arrow-up"></i></span>
                                                    <span class="btn btn-xs btn-link move-down"><i
                                                                class="glyphicon glyphicon-arrow-down"></i></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if ($currentParent): ?>
                                <?php if (Yii::$app->user->can('product-category/create')): ?>
                                    <span class="btn btn-default btn-block add-btn" data-toggle="modal"
                                          data-target="#myModal"
                                          data-whatever="新增二级分类" data-pid="<?= $currentParent->id ?>">新增</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="myModal" role="dialog" aria-labelledby="myModalLabel">
        <?php
        $model = new \common\models\ProductCategory();
        $model->parent_id = $id;
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['product-category/create'],
            'enableAjaxValidation' => true,
            'validationUrl' => ['product-category/validation'],
            'id' => 'product-category-form',
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
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">新增分类</h4>
                </div>
                <div class="spiner-example loading" style="display: block">
                    <div class="sk-spinner sk-spinner-three-bounce">
                        <div class="sk-bounce1"></div>
                        <div class="sk-bounce2"></div>
                        <div class="sk-bounce3"></div>
                    </div>
                </div>


                <div class="modal-body input_box" style="display: none">

                    <?= \yii\bootstrap\Html::activeHiddenInput($model, 'parent_id', ['id' => 'parent_id']) ?>
                    <?= $form->field($model, 'name')->textInput() ?>
                    <?= $form->field($model, 'is_show_nav')->checkbox(['id' => 'show_nav']) ?>
                    <?= $form->field($model, 'is_show_list')->checkbox() ?>
                    <?= $form->field($model, 'title')->textInput() ?>
                    <?= $form->field($model, 'keywords')->textarea() ?>
                    <?= $form->field($model, 'description')->textarea() ?>
                    <?= $form->field($model, 'customer_service_link')->textarea() ?>

                    <div id="file-content">
                        <?php $field = $form->field($model, 'image')->hiddenInput(['id' => 'productcategory_image_key'])->hint('图片要求，宽高：85px &times; 65px，格式：JPG');
                        $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                                'buttonTitle' => '上传图片',
                                'name' => 'file',
                                'serverUrl' => ['upload'],
                                'formData' =>[
                                    Yii::$app->request->csrfParam => Yii::$app->request->csrfToken,
                                ],
                                'done' => new \yii\web\JsExpression(<<<JS
                                        function(e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-link_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#image").empty().append($("<div class=\"thumbnail pull-left\"></div>").append($("<img />").attr("src", file.thumbnailUrl)));
                                                $("#productcategory_image_key").val(file.key);
                                                $("#productcategory_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }
JS
                                )]) . '<div id="image"></div>'
                        ?>
                        <?= $field ?>
                    </div>
                    <div id="file-content-banner">
                        <?php $field = $form->field($model, 'banner_image')->hiddenInput(['id' => 'productcategory_banner_image_key'])->hint('图片要求，格式：JPG');
                        $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                                'buttonTitle' => '上传图片',
                                'name' => 'file',
                                'serverUrl' => ['upload'],
                                'formData' =>[
                                    Yii::$app->request->csrfParam => Yii::$app->request->csrfToken,
                                ],
                                'done' => new \yii\web\JsExpression(<<<JS
                                        function(e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-link_banner_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#banner_image").empty().append($("<div class=\"thumbnail pull-left\"></div>").append($("<img />").attr("src", file.thumbnailUrl)));
                                                $("#productcategory_banner_image_key").val(file.key);
                                                $("#productcategory_banner_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }
JS
                                )]) . '<div id="banner_image"></div>'
                        ?>
                        <?= $field ?>
                        <?= $form->field($model, 'banner_url')->textarea() ?>
                    </div>
                    <div id="file-content-icon">
                        <?php $field = $form->field($model, 'icon_image')->hiddenInput(['id' => 'productcategory_icon_image_key'])->hint('图片要求，格式：JPG、PNG');
                        $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                                'buttonTitle' => '上传图标',
                                'name' => 'file',
                                'serverUrl' => ['upload'],
                                'formData' =>[
                                    Yii::$app->request->csrfParam => Yii::$app->request->csrfToken,
                                ],
                                'done' => new \yii\web\JsExpression(<<<JS
                                        function(e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-link_icon_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#icon_image").empty().append($("<div class=\"thumbnail pull-left\"></div>").append($("<img />").attr("src", file.thumbnailUrl)));
                                                $("#productcategory_icon_image_key").val(file.key);
                                                $("#productcategory_icon_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }
JS
                                )]) . '<div id="icon_image"></div>'
                        ?>
                        <?= $field ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>



    <div class="modal fade" id="myModal3" role="dialog" aria-labelledby="myModalLabel">
         <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel"><span class="spans">一级seo设置</span></h4>

                </div>

           <?php 
              $model = new \backend\models\ClassificationProductsForm();
              $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['product-category/seo'],
                'enableAjaxValidation' => true,
                'validationUrl' => ['product-category/validation'],
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-2',
                        'offset' => 'col-sm-offset-2',
                        'wrapper' => 'col-sm-8',
                        'hint' => 'col-sm-offset-2 col-sm-8'
                    ],
                ],
            ]);
              ?>
                   <?= $form->field($model, 'title')->textInput() ?>
                   <?= $form->field($model, 'keywords')->textarea() ?>
                   <?= $form->field($model, 'description')->textarea() ?>
                   <?= $form->field($model, 'content')->widget(CKEditorWidget::className()); ?>
           
                   <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                  </div>

        <?php \yii\bootstrap\ActiveForm::end(); ?>      
         </div>
      </div>  
    </div>

    <div class="modal fade" id="myModal2" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">删除分类</h4>
                </div>
                <div class="modal-body">
                    确定删除此分类吗?
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary sure-btn">确定</button>
                </div>
            </div>
        </div>
    </div>
<?php \backend\assets\SortAsset::register($this); ?>
<?php

$productCategoryCreateUrl = \yii\helpers\Url::to(['create']);
$productCategoryUpdateUrl = \yii\helpers\Url::to(['update', 'id' => '__id__']);
$productCategorySeoUrl = \yii\helpers\Url::to(['seo', 'id' => '__id__']);
$productCategorySortUrl = \yii\helpers\Url::to(['sort']);
$productCategoryDeleteUrl = \yii\helpers\Url::to(['delete']);
$productCategoryDetailUrl = \yii\helpers\Url::to(['detail', 'id' => '__id__']);
$this->registerJs(<<<JS
	$('.sortablelist').clickSort({
        speed:200,
        moveCallback: function(source_id, target_id){
            $.post('{$productCategorySortUrl}', {source_id: source_id, target_id: target_id}, function(rs){
            }, 'json');
        },
        callback:function(){
            setTimeout(function(){
                $('.sortableitem').find('.move-up,.move-down').show();
                var div1 = $('.so1:first');
                var div2 = $('.so1:last');
                var div3 = $('.so2:first');
                var div4 = $('.so2:last');
                var div5 = $('.so3:first');
                var div6 = $('.so3:last');
                div1.find('.move-up').hide();
                div2.find('.move-down').hide();
                div3.find('.move-up').hide();
                div4.find('.move-down').hide();
                div5.find('.move-up').hide();
                div6.find('.move-down').hide();
            }, 30);
        }

    });
    $('.sortablelist').find('.move-up,.move-down').show();
    var div1 = $('.so1:first');
    var div2 = $('.so1:last');
    var div3 = $('.so2:first');
    var div4 = $('.so2:last');
    var div5 = $('.so3:first');
    var div6 = $('.so3:last');
    div1.find('.move-up').hide();
    div2.find('.move-down').hide();
    div3.find('.move-up').hide();
    div4.find('.move-down').hide();
    div5.find('.move-up').hide();
    div6.find('.move-down').hide();
    
    var inSaving = false;
    $('#product-category-form').on('beforeSubmit', function(){
        if(inSaving) return false;
        inSaving = true; // 防止重复提交
        return true;
    });
    $('#myModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');
        var modal = $(this);
        modal.find('.modal-title').text(recipient);
    });

	$('.delete-btn').on('click',function(){
	    $('.warning-active').html('');
	    var delete_id = $(this).parents('.sortableitem').attr('data-id');
	    $('.sure-btn').unbind('click');
	    $('.sure-btn').on('click',function(){
	        $.post('{$productCategoryDeleteUrl}',{id:delete_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	            window.location.reload();
	        }
	    },'json')
	    })
	})
	
	$('#show_nav').click(function(){
        if($(this).is(':checked')){
            $(this).val(1);  
        }
    })
    
    var createAction = '{$productCategoryCreateUrl}';
    var updateAction = '{$productCategoryUpdateUrl}';
    $('.add-btn').on('click',function(){
        $('.loading').hide();
        $('.modal-body').show();
        $('.modal form').attr('action', createAction);
        var parent_id = $(this).attr('data-pid');
        $('#product-category-form').trigger('reset.yiiActiveForm');
        $('#image').html('');
        $('#parent_id').val(parent_id);
        if(parent_id==0)
        {
            $('#file-content').css('display','none');
            $('#file-content-banner').css('display','block');
            //$('#file-content-icon').css('display','none');
            //$('.field-show_nav').show();
        }
        else
        {
            $('#file-content').css('display','block');
            $('#file-content-banner').css('display','none');
            //$('#file-content-icon').css('display','block');
            //$('.field-show_nav').hide();
            $('.field-productcategory-customer_service_link').hide();
        }
    })      


    var seoAction = '{$productCategorySeoUrl}';
     $('.seo-btn').on('click',function(){
        var id = $(this).parents('.list-group-item').attr('data-id');
        var seo_url = seoAction.replace('__id__', id);
        $('.modal form').attr('action', seo_url);
              $.get(seo_url,{id:id},function(rs){
          $('#classificationproductsform-title').val(rs.title);
          $('#classificationproductsform-keywords').val(rs.keywords);
          $('#classificationproductsform-description').val(rs.description);
          $('#classificationproductsform-content').val(rs.content);
          if(rs.parent_id != 0){
                $('.spans').html("二级分类SEO设置");
           }else{
             $('.spans').html("一级分类SEO设置");
           }
         })
         
         
        $('.sure-btn').unbind('click');
        $('.sure-btn').on('click',function(){
            $.post(seo_url,{id:id},function(rs){
         })
        })
    })

        $('.seo-btn').on('click',function(){
        $('.warning-active').html('');
        var delete_id = $(this).parents('.sortableitem').attr('data-id');
        $('.sure-btn').unbind('click');
        $('.sure-btn').on('click',function(){
            $.post('{$productCategorySeoUrl}',{id:delete_id},function(rs){
            if(rs.status != 200){
                $('.warning-active').html(rs.message);
            }else{
                window.location.reload();
            }
        },'json')
        })
    })
    
    $('.update-btn').on('click',function(){
        var id = $(this).parents('.list-group-item').attr('data-id');
        $('.modal form').attr('action', updateAction.replace('__id__', id));
        $.get('{$productCategoryDetailUrl}'.replace('__id__', id),function(rs){
            if(rs.status!=200){
            }else{
                $('.loading').hide();
                $('.modal-body').show();
                $('#parent_id').val(rs.model.parent_id);
                rs.model.is_show_nav ? $('#show_nav').prop('checked', true) : $('#show_nav').prop('checked', false);
                rs.model.is_show_list ? $('#productcategory-is_show_list').prop('checked', true) : $('#productcategory-is_show_list').prop('checked', false);
                if(rs.model.parent_id==0)
                {
                    $('#file-content').css('display','none');
                    $('#file-content-banner').css('display','block');
                    //$('#file-content-icon').css('display','none');
                    //$('.field-show_nav').show();
                    $('.field-productcategory-customer_service_link').show();
                }
                else
                {
                    $('#file-content').css('display','block');
                    $('#file-content-banner').css('display','none');
                    //$('#file-content-icon').css('display','block');
                    //$('.field-show_nav').hide();
                    $('.field-productcategory-customer_service_link').hide();
                }
                $('#productcategory-name').val(rs.model.name);
                $('.field-productcategory-title').css('display','none');
                $('.field-productcategory-keywords').css('display','none');
                $('.field-productcategory-description').css('display','none');
                $('#productcategory_image_key').val(rs.model.image);
                $('#productcategory_icon_image_key').val(rs.model['icon_image']);
                $('#productcategory-url').val(rs.model.url);
                $('#productcategory-banner_url').val(rs.model.banner_url);
                $('#productcategory-customer_service_link').val(rs.model.customer_service_link);
                if(rs.model.image)
                {
                    $('#image').empty().append($('<div class="thumbnail pull-left"></div>').append($('<img />').attr('src', rs.model.imageUrl)));
                }
                else
                {
                    $('#image').empty();
                }
                if(rs.model['banner_image'])
                {
                    $('#banner_image').empty().append($('<div class="thumbnail pull-left"></div>').append($('<img />').attr('src', rs.model.bannerImageUrl)));
                }
                else
                {
                    $('#banner_image').empty();
                }
                if(rs.model['icon_image'])
                {
                    $('#icon_image').empty().append($('<div class="thumbnail pull-left"></div>').append($('<img />').attr('src', rs.model.iconImageUrl)));
                }
                else
                {
                    $('#icon_image').empty();
                }
            }
        })
    })
JS
);
?>