<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use common\actions\RedirectStatisticsAction;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $provider yii\data\ActiveDataProvider */
/* @var $target integer */

$this->title = '焦点图管理';
$this->params['breadcrumbs'][] = $this->title;
/** @var \common\models\Banner[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
$imageStorage = Yii::$app->get('imageStorage');

?>
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li<?php if ($target == '1'): ?> class="active"<?php endif; ?>>
                            <a href="<?= Url::to(['list', 'target' => '1']) ?>">电脑端</a>
                        </li>
                        <li<?php if ($target == '2'): ?> class="active"<?php endif; ?>>
                            <a href="<?= Url::to(['list', 'target' => '2']) ?>">手机网页</a>
                        </li>
                    </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <div class="panel-body">
                            <div class="ibox-tools">
                                <?php if (Yii::$app->user->can('banner/create')): ?>
                                    <a href="#" class="btn btn-primary btn-sm add-banner" data-target="#add_banner"
                                       data-toggle="modal"><span class="fa fa-plus"></span> 新增</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="panel-body">

                            <ul class="list-group sortablelist">
                                <li class="list-group-item">
                                    <div class="row">
                                        <span class="col-xs-2">图片</span>
                                        <span class="col-xs-4">标题/链接</span>
                                        <span class="col-xs-2">UV</span>
                                        <span class="col-xs-2">PV</span>
                                        <span class="text-right col-xs-2">操作</span>
                                    </div>
                                </li>
                                <?php foreach ($models as $model):
                                    $uv = RedirectStatisticsAction::getUv($model->id);
                                    $pv = RedirectStatisticsAction::getPv($model->id);
                                    ?>
                                    <li class="list-group-item so1 sortableitem" data-id="<?= $model->id ?>">
                                        <div class="row">
                                            <div class="col-xs-2">
                                                <?= Html::img($model->getImageUrl(), ['class' => 'thumbnail margin0']); ?>
                                            </div>
                                            <div class="col-xs-4">
                                                <span><?= $model->title ?><br><?= Html::a($model->url, $model->url); ?></span>
                                            </div>
                                            <div class="col-xs-2">
                                                <span class="text-muted">总数：<?= $model->uv + $uv ?></span>
                                                <br>
                                                <span class="text-success">今日：<?= $uv; ?></span>
                                            </div>
                                            <div class="col-xs-2">
                                                <span class="text-muted">总数：<?= $model->pv + $pv ?></span>
                                                <br>
                                                <span class="text-success">今日：<?= $pv; ?></span>
                                            </div>
                                            <div class="text-right col-xs-2" data-id="<?= $model->id ?>">
                                                <?php if (Yii::$app->user->can('banner/update')): ?>
                                                    <span class="btn btn-xs btn-link move-up"><i
                                                                class="glyphicon glyphicon-arrow-up"></i></span>
                                                    <span class="btn btn-xs btn-link move-down"><i
                                                                class="glyphicon glyphicon-arrow-down"></i></span>
                                                    <span class="btn btn-xs btn-white update-btn" data-target="#add_banner"
                                                          data-toggle="modal" data-whatever="编辑焦点图">编辑</span>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('banner/delete')): ?>
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
        </div>
    </div>
<?php \backend\assets\SortAsset::register($this); ?>
<?php if (Yii::$app->user->can('banner/create') || Yii::$app->user->can('banner/update')): ?>
    <div class="modal fade" id="add_banner" role="dialog" aria-labelledby="modal-title">
        <?php
        $model = new \common\models\Banner();
        $model->target = $target;
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['banner/create'],
            'validationUrl' => ['banner/validation'],
            'enableAjaxValidation' => true,
            'id' => 'banner-form',
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
                    <h4 class="modal-title">新增焦点图</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>
                    <?php $field = $form->field($model, 'image')->hiddenInput(['id' => 'banner_image_key'])->hint($target == '1' ? '图片要求，宽高：1920px &times; 480px，格式：JPG' : '图片要求，宽高：待定px &times; 待定px，格式：JPG');
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
                                                $(".field-banner_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#image").empty().append($("<img class=\"thumbnail margin0\" />").attr("src", file.thumbnailUrl));
                                                $("#banner_image_key").val(file.key);
                                                $("#banner_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }')
                        ]) . '<div id="image"></div>'
                    ?>
                    <?= $field ?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <?= Html::activeHiddenInput($model, 'target')?>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>
<?php endif; ?>
<?php if (Yii::$app->user->can('banner/delete')): ?>
    <div class="modal fade" id="delete_Carousel" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">删除焦点图</h4>
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
    $('.sortablelist').clickSort({
            speed:200,
            moveCallback: function(source_id, target_id){
            $.post('" . \yii\helpers\Url::to(['banner/sort']) . "', {source_id: source_id, target_id: target_id}, function(rs){
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
        
        $('#add_banner').on('show.bs.modal', function (event) {
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
    
    $('.add-banner').on('click',function(){
        $('.modal form').attr('action', createAction1);
        $('.input_box input').val('');
        $('#image').empty();
        $('#banner-form').trigger('reset.yiiActiveForm');
    });

    
    $('.update-btn').on('click',function(){
        $('#banner-form').trigger('reset.yiiActiveForm');
        var id = $(this).parents('.list-group-item').attr('data-id');
        var updateAction = '" . \yii\helpers\Url::to(['update', 'id' => '__id__']) . "';
        $('.modal form').attr('action', updateAction.replace('__id__', id));
        $.get('" . \yii\helpers\Url::to(['detail', 'id' => '__id__']) . "'.replace('__id__', id),function(rs){
            if(rs.status!=200){
                
            }else{
                $('#banner-title').val(rs.model.title);
                $('#banner_image_key').val(rs.model.image);
                $('#banner-url').val(rs.model.url);
                $('#image').empty().append($('<div class=\"thumbnail pull-left\"></div>').append($('<img />').attr('src', rs.model.imageUrl)));
            }
        },'json')   
    })   
    ");
?>