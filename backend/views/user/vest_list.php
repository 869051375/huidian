<?php

/* @var $this yii\web\View */
use backend\models\UserSearch;
use backend\widgets\LinkPager;
use yii\helpers\Url;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel UserSearch */

$this->title = '马甲用户管理';
$this->params['breadcrumbs'][] = $this->title;
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$imageStorage = Yii::$app->get('imageStorage');

?>
    <div class="row">
        <div class="col-xs-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>马甲用户列表 </h5>
                    <?php if(Yii::$app->user->can('user/create_vest')): ?>
                    <div class="ibox-tools">
                        <a href="#" class="btn btn-primary btn-sm add-Carousel" data-target="#add_Carousel" data-toggle="modal"><span class="fa fa-plus"></span> 新增</a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="ibox-content">
                    <?php
                    $labelOptions = ['labelOptions' => ['class' => false]];
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'action' => ['vest-list'],
                        'layout' => 'inline',
                        'method' => 'get',
                    ]); ?>
                    <?= $form->field($searchModel, 'keyword', $labelOptions)->textInput(['placeholder'=>'请输入手机号/姓名']) ?>
                    <button type="submit" class="btn btn-default">搜索</button>
                    <?php \yii\bootstrap\ActiveForm::end(); ?>
                </div>
                <div class="ibox-content">
                    <ul class="list-group sortablelist">
                        <li class="list-group-item">

                            <div class="row">
                                <span class="col-xs-1">姓名/昵称</span>
                                <span class="col-xs-2">账号</span>
                                <span class="col-xs-2">手机号</span>
                                <span class="col-xs-2">常用邮箱</span>
                                <span class="col-xs-2">创建时间</span>
                                <span class="col-xs-2">最后登录时间</span>
                                <span class="col-xs-1">操作</span>
                            </div>
                        </li>
                        <?php foreach ($models as $model):?>
                            <li class="list-group-item so1 sortableitem" data-id="<?= $model->id?>">
                                <div class="row">
                                    <div class="col-xs-1">
                                        <?= $model->name?>
                                    </div>
                                    <div class="col-xs-2">
                                        <?= $model->username?>
                                    </div>
                                    <div class="col-xs-2">
                                        <?= $model->phone?>
                                    </div>
                                    <div class="col-xs-2">
                                        <?= $model->email?>
                                    </div>
                                    <div class="col-xs-2">
                                        <?= Yii::$app->formatter->asDatetime($model->created_at);?>
                                    </div>
                                    <div class="col-xs-2">
                                        <?php  if($model->last_login==0): ?>
                                            从未
                                        <?php else: ?>
                                            <?= Yii::$app->formatter->asDatetime($model->last_login);?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-xs-1">
                                    <?php if (Yii::$app->user->can('order/create')): ?>
                                        <a href="<?= Url::to(['order/create', 'user_id' => $model->id])?>" class="btn btn-xs btn-primary">代客下单</a>
                                    <?php endif;?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                    <div class="row">
                        <div class="margin-auto">
                            <?= LinkPager::widget(['pagination' => $pagination]); ?>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>
<?php if(Yii::$app->user->can('user/create_vest')): ?>
    <div class="modal fade" id="add_Carousel" role="dialog" aria-labelledby="modal-title">
        <?php
        $model = new \common\models\User();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['user/create'],
            'validationUrl' => ['user/validation'],
            'enableAjaxValidation' => true,
            'id' => 'user-form',
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
                    <h4 class="modal-title">新增马甲用户</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($mode, 'phone')->textInput() ?>
                    <?= $form->field($mode, 'name')->textInput() ?>
                    <?= $form->field($mode, 'email')->textInput() ?>
                    <?= $form->field($mode, 'password')->passwordInput() ?>
                    <?= $form->field($mode, 'tpassword')->passwordInput() ?>
                    <?= $form->field($mode, 'address')->textInput() ?>
                    <?= $form->field($mode, 'is_vest')->hiddenInput() ?>
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
<?php
$this->registerJs("
    $('.sortablelist').clickSort({
            speed:200,
            moveCallback: function(source_id, target_id){
            $.post('" . Url::to(['user/sort']) . "', {source_id: source_id, target_id: target_id}, function(rs){
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
        
        $('#add_Carousel').on('show.bs.modal', function (event) {
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
	        $.post('" . Url::to(['delete']) . "',{id:delete_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	        
	            window.location.reload();
	        }
	    },'json')
	    })
	})

    var createAction1 = '" . Url::to(['create']) . "';
    
    $('.add-Carousel').on('click',function(){
        $('.modal form').attr('action', createAction1);
        $('.input_box input').val('');
        $('#image').empty();
        $('#link-form').trigger('reset.yiiActiveForm');
    });
    
    
    ");
?>