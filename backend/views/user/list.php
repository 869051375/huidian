<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use common\models\User;
use imxiangli\select2\Select2Widget;
use yii\helpers\Url;
use zhuravljov\yii\widgets\DateTimePicker;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var \backend\models\UserSearch $searchModel */
/* @var \backend\models\SignupForm $mode */

$this->title = '注册用户管理';
$this->params['breadcrumbs'][] = $this->title;
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$imageStorage = Yii::$app->get('imageStorage');

?>
    <div class="row">
        <div class="col-xs-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>注册用户管理 </h5>
                    <?php if (Yii::$app->user->can('user/create')): ?>
                    <div class="ibox-tools">
                        <a href="#" class="btn btn-primary btn-sm add-Carousel" data-target="#add_Carousel" data-toggle="modal"><span class="fa fa-plus"></span> 新增</a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="ibox-content">
                    <?php
                    $labelOptions = ['labelOptions' => ['class' => false]];
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'action' => ['list'],
                        'layout' => 'inline',
                        'method' => 'get',
                    ]); ?>
                    <b>创建时间</b>
                    <?= $form->field($searchModel, 'starting_time')->widget(DateTimePicker::className(), [
                        'clientOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'language' => 'zh-CN',
                            'autoclose' => true,
                            'minView' => 'month',
                        ],
                        'clientEvents' => [],
                    ]) ?>
                    <?= $form->field($searchModel, 'end_time')->widget(DateTimePicker::className(), [
                        'clientOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'language' => 'zh-CN',
                            'autoclose' => true,
                            'minView' => 'month',
                        ],
                        'clientEvents' => [],
                    ]) ?>
                    <div class="page-select2-area" style="display: inline-block;">
                    <b>客户来源</b><?= $form->field($searchModel, 'source')->widget(Select2Widget::className(),[
                            'serverUrl' => \yii\helpers\Url::to(['customer-source/ajax-list', 'is_flag' => '0']),
                            'itemsName' => 'source',
                            'selectedItem' => $searchModel->customerSource ? [$searchModel->customerSource->id => $searchModel->customerSource->name] : [],
                            'options' => ['class' => 'form-control', 'prompt'=>'请选择来源'],
                            'nameField' => 'name',
                            'placeholderId' => '0',
                            'placeholder' => '请选择来源',
                            'searchKeywordName' => 'keyword',
                        ])?>
					</div>
					<div class="page-select2-area" style="display: inline-block;">
                    <?= $form->field($searchModel, 'user_from', $labelOptions)->widget(Select2Widget::className(), [
                        'selectedItem' => \backend\models\UserSearch::getUserFrom(),
                        'placeholderId' => '0',
                        'placeholder' => '请选择类型',
                        'options' => ['class' => 'form-control', 'prompt'=>'请选择类型'],
                        'static' => true,
                    ]) ?>
                    </div>
                    <br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <div class="page-select2-area" style="display: inline-block;">
                    <?= $form->field($searchModel, 'type', $labelOptions)->widget(Select2Widget::className(), [
                        'selectedItem' => \backend\models\UserSearch::getTypes(),
                        'placeholderId' => '0',
                        'placeholder' => '请选择类型',
                        'options' => ['class' => 'form-control', 'prompt'=>'请选择类型'],
                        'static' => true,
                    ]) ?>
                    </div>
                    <?= $form->field($searchModel, 'keyword', $labelOptions)->textInput(['placeholder'=>'请输入关键词']) ?>
                    <button type="submit" class="btn btn-default">搜索</button>
                    <?php \yii\bootstrap\ActiveForm::end(); ?>
                </div>
                <div class="ibox-content">

                    <table class="table">
                        <thead>
                        <tr>
                            <th>用户ID</th>
                            <th>姓名/昵称</th>
                            <th>手机号</th>
                            <th>常用邮箱</th>
                            <th>用户来源</th>
                            <th>创建时间</th>
                            <th>最后登录时间</th>
                            <th>注册方式</th>
                            <th>关联订单</th>
                            <th class="text-right">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        /**@var User[] $models **/
                        foreach ($models as $model): ?>
                            <tr>
                                <td><?= $model->id?></td>
                                <td><?= $model->name?></td>
                                <td><?= $model->phone?></td>
                                <td><?= $model->email?></td>
                                <td>
                                    <?php if($model->source_name): ?>
                                    <?= $model->source_name; ?>
                                    <?php else: ?>
                                        --
                                    <?php endif; ?>
                                </td>
                                <td><?= $model->isRegister() ? Yii::$app->formatter->asDatetime($model->created_at) : '未注册';?></td>
                                <td><?= $model->getLastTime() ?></td>
                                <td>
                                    <?php if($model->customer->getRegisterMode() == \common\models\CrmCustomer::REGISTER_MODE_ADD):?>
                                        <?= $model->creator_name ?>
                                    <?php else: ?>
                                        客户
                                    <?php endif; ?>
                                    <br />
                                    <?= $model->customer->getRegisterModeName() ?>
                                </td>
                                <td>
                                    <?= '未付：'.$model->getPendingOrderCount(); ?><br>
                                    <?= '已付：'.$model->getPaidOrderCount(); ?>
                                </td>
                                <td class="text-right  data-action">
                                    <?php if (Yii::$app->user->can('customer-detail/*')): ?>
                                        <a href="<?= Url::to(['customer-detail/business-subject', 'id' => $model->customer_id])?>" class="btn btn-xs btn-default" target="_blank">查看详情</a>
                                    <?php endif;?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="row">
                        <div class="margin-auto">
                            <?= LinkPager::widget(['pagination' => $pagination]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php if (Yii::$app->user->can('user/create')): ?>
    <div class="modal fade" id="add_Carousel" role="dialog" aria-labelledby="modal-title">
        <?php
        $model = new User();
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
                    <h4 class="modal-title">新增用户</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($mode, 'phone')->textInput() ?>
                    <?= $form->field($mode, 'name')->textInput() ?>
                    <?= $form->field($mode, 'email')->textInput() ?>
                    <?= $form->field($mode, 'password')->passwordInput() ?>
                    <?= $form->field($mode, 'tpassword')->passwordInput() ?>
                    <?= $form->field($mode, 'address')->textInput() ?>
                    <?= $form->field($mode, 'is_vest')->hiddenInput(['value'=>'0']) ?>
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
//    $('.sortablelist').clickSort({
//            speed:200,
//            moveCallback: function(source_id, target_id){
//            $.post('" . Url::to(['user/sort']) . "', {source_id: source_id, target_id: target_id}, function(rs){
//            }, 'json');
//        },
//            callback:function(){
//                setTimeout(function(){
//                    $('.sortableitem').find('.move-up,.move-down').show();
//                    var div1 = $('.so1:first');
//                    var div2 = $('.so1:last');
//                    div1.find('.move-up').hide();
//                    div2.find('.move-down').hide();
//                }, 30);
//            }
//        });
//        $('.sortablelist').find('.move-up,.move-down').show();
//        var div1 = $('.so1:first');
//        var div2 = $('.so1:last');
//        div1.find('.move-up').hide();
//        div2.find('.move-down').hide();
        
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