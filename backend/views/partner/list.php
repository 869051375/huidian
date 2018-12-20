<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use yii\bootstrap\Html;

/* @var $provider yii\data\ActiveDataProvider */

$this->title = '合作客户管理';
$this->params['breadcrumbs'][] = $this->title;
/** @var common\models\Partner[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
$imageStorage = Yii::$app->get('imageStorage');

?>
    <div class="row">
        <div class="col-xs-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>合作客户列表 </h5>
                    <div class="ibox-tools">
                        <?php if (Yii::$app->user->can('partner/create')): ?>
                            <a href="#" class="btn btn-primary btn-sm add-partner" data-target="#add-partner"
                               data-toggle="modal"><span class="fa fa-plus"></span> 新增</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ibox-content">
                    <ul class="list-group sortablelist">
                        <li class="list-group-item">
                            <div class="row">
                                <span class="col-xs-4">图片</span>
                                <span class="col-xs-4">排序值</span>
                                <span class="col-xs-4">操作</span>
                            </div>
                        </li>
                        <?php foreach ($models as $model): ?>
                            <li class="list-group-item so1 sortableitem" data-id="<?= $model->id ?>">
                                <div class="row">
                                    <div class="col-xs-4">
                                        <?= Html::img($model->getImageUrl(), ['class' => 'thumbnail margin0']); ?>
                                    </div>
                                    <div class="col-xs-4">
                                        <?= $model->sort ?>
                                    </div>
                                    <div class="col-xs-4" data-id="<?= $model->id ?>">
                                        <?php if (Yii::$app->user->can('partner/update')): ?>
                                            <span class="btn btn-xs btn-white update-btn" data-target="#add-partner"
                                                  data-toggle="modal" data-whatever="编辑合作客户">编辑</span>
                                        <?php endif; ?>
                                        <?php if (Yii::$app->user->can('partner/delete')): ?>
                                            <span class="btn btn-xs btn-white delete-btn" data-target="#delete_Carousel"
                                                  data-toggle="modal" data-id="<?= $model->id ?>">删除</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                    <div class="row">
                        <div class="margin-auto">
                            <?=
                            LinkPager::widget([
                                'pagination' => $pagination
                            ]);
                            ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php \backend\assets\SortAsset::register($this); ?>
<?php if (Yii::$app->user->can('partner/create') || Yii::$app->user->can('partner/update')): ?>
    <div class="modal fade" id="add-partner" role="dialog" aria-labelledby="modal-title">
        <?php
        $model = new \common\models\Partner();
        $model->loadDefaultValues();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['partner/create'],
            'validationUrl' => ['partner/validation'],
            'enableAjaxValidation' => true,
            'id' => 'partner-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-2',
                    'offset' => 'col-sm-offset-2',
                    'wrapper' => 'col-sm-8',
                ],
            ],
        ]); ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">新增合作客户</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'introduce')->textarea() ?>
                    <?= $form->field($model, 'sort')->textInput(['maxlength' => true]) ?>
                    <?php $field = $form->field($model, 'image')->hiddenInput(['id' => 'partner_image_key']);
                    $field->parts['{input}'] = $field->parts['{input}'] . \imxiangli\upload\JQFileUpLoadWidget::widget([
                            'buttonTitle' => '上传图片',
                            'name' => 'file',
                            'serverUrl' => ['upload'],
                            'formData' =>[
                                Yii::$app->request->csrfParam => Yii::$app->request->csrfToken,
                            ],
                            'done' => new \yii\web\JsExpression('function (e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-partner_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#image").empty().append($("<img class=\"thumbnail margin0\" />").attr("src", file.thumbnailUrl));
                                                $("#partner_image_key").val(file.key);
                                                $("#partner_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }')
                        ]) . '<div id="image"></div>'
                    ?>
                    <?= $field ?>
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
<?php if (Yii::$app->user->can('partner/delete')): ?>
    <div class="modal fade" id="delete_Carousel" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">删除合作客户</h4>
                </div>
                <div class="modal-body">
                    确定删除吗?
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary sure-btn">确定</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php
$this->registerJs("
    
    $('#add-partner').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');
        var modal = $(this);
        modal.find('.modal-title').text(recipient);
    });

    $('.cancel-btn').on('click',function(){
        $('.warning-active').html('');
    })

	$('.delete-btn').on('click',function(){
	    var delete_id = $(this).attr('data-id');
	    $('.sure-btn').on('click',function(){
	        $.post('" . \yii\helpers\Url::to(['delete']) . "',{id:delete_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	        
	            window.location.reload();
	        }
	    },'json')
	    })
	})

    var createAction1 = '" . \yii\helpers\Url::to(['create']) . "';
    $('.add-partner').on('click',function(){
        $('.modal form').attr('action', createAction1);
        $('.input_box input').val('');
        $('#image').empty();
        $('#partner-form').trigger('reset.yiiActiveForm');
    });
 
    $('.update-btn').on('click',function(){
        $('#partner-form').trigger('reset.yiiActiveForm');
        var id = $(this).parents('.list-group-item').attr('data-id');
        var updateAction = '" . \yii\helpers\Url::to(['update', 'id' => '__id__']) . "';
        $('.modal form').attr('action', updateAction.replace('__id__', id));
        $.get('" . \yii\helpers\Url::to(['detail', 'id' => '__id__']) . "'.replace('__id__', id),function(rs){
            if(rs.status!=200){
                
            }else{
                $('#partner-name').val(rs.model.name);
                $('#partner_image_key').val(rs.model.image);
                $('#partner-url').val(rs.model.url);
                $('#partner-introduce').val(rs.model.introduce);
                $('#partner-sort').val(rs.model.sort);
                $('#image').empty().append($('<div class=\"thumbnail pull-left\"></div>').append($('<img />').attr('src', rs.model.imageUrl)));
            }
        },'json')
        
    })   
    ");
?>