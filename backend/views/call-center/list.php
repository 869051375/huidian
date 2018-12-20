<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $provider yii\data\ActiveDataProvider */
/* @var $target integer */

$this->title = '外呼集成设置';
$this->params['breadcrumbs'][] = $this->title;
$models = $provider->getModels();
$pagination = $provider->getPagination();
$imageStorage = Yii::$app->get('imageStorage');
\toxor88\switchery\SwitcheryAsset::register($this);
 \backend\assets\SortAsset::register($this);
?>
<div class="row">
    <div class="col-xs-12">
        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="<?= Url::to(['list']) ?>">Callcenter集成</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel-body">
                        <div class="ibox-tools">
                            <?php if (Yii::$app->user->can('call-center/*')): ?>
                                <a href="#" class="btn btn-primary btn-sm add-Carousel" data-target="#add_Carousel" data-toggle="modal">
                                    <span class="fa fa-plus"></span>新增集成
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <ul class="list-group sortablelist">
                            <li class="list-group-item">
                                <div class="row">
                                    <span class="col-xs-1">id</span>
                                    <span class="col-xs-3">描述名称</span>
                                    <span class="col-xs-5">对接地址</span>
                                    <span class="col-xs-1">状态</span>
                                    <span class="col-xs-2 text-right">操作</span>
                                </div>
                            </li>
                            <?php foreach ($models as $model):
                                $options = [
                                    'id' => false,
                                    'class' => 'change-status-checkbox',
                                    'label' => false,
                                    'data-id' => $model->id,
                                ];
                                if (!Yii::$app->user->can('call-center/*')) {
                                    $options['readonly'] = 'readonly';
                                }
                                ?>
                                <li class="list-group-item so1 sortableitem" data-id="<?= $model->id?>">
                                    <div class="row">
                                        <div class="col-xs-1"><?= $model->id;?></div>
                                        <div class="col-xs-3"><?= $model->name;?></div>
                                        <div class="col-xs-5"><?= $model->url;?></div>
                                        <div class="col-xs-1">
                                            <label>
                                                <?= Html::activeCheckbox($model, 'status', $options); ?>
                                            </label>
                                        </div>
                                        <div class="col-xs-2 text-right">
                                            <?php if (Yii::$app->user->can('call-center/*')): ?>
                                                <a href="<?= Url::to(['update', 'id' => $model->id]);?>">编辑</a>
<!--                                                <a href="http://a1.7x24cc.com/commonInte?flag=104&account=N00000001033&phonenum=18612414324&integratedid=5001&key=8f299862-9033-4185-9076-c7f9e035a051">编辑</a>-->
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

<div class="modal fade" id="add_Carousel" role="dialog" aria-labelledby="modal-title">
    <?php
    $model = new \common\models\CallCenter();
    $model->loadDefaultValues();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['call-center/create'],
        'validationUrl' => ['call-center/validation'],
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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">新增Callcenter集成</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($model, 'name')->textInput(['id' => 'call-center-name']) ?>
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

<!---删除start-->
<div class="modal fade" id="delete_Carousel" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">删除Callcenter集成</h4>
            </div>
            <div class="modal-body">
                确定删除吗?
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary delete-sure-btn">确定</button>
            </div>
        </div>
    </div>
</div>
<?php
$deleteUrl = \yii\helpers\Url::to(['ajax-delete']);
$this->registerJs(<<<JS
    $('.delete-btn').unbind();
    $('.delete-btn').click(function(){
        var delete_id = $(this).attr('data-id');
        $('.delete-sure-btn').unbind();
        $('.delete-sure-btn').click(function(){
            $.post('{$deleteUrl}',{id:delete_id},function(rs){
                if(rs.status != 200){
                    $('.warning-active').html(rs.message);
                }else{
                    window.location.reload();
                }
            },'json');
        });
    });
JS
);?>
<!---删除end-->

<!--启用start-->
<div class="modal fade" id="up-down-modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">修改状态</h4>
            </div>
            <div class="modal-body">
                确定启用吗?
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary sure-btn">确定</button>
            </div>
        </div>
    </div>
    <?php
    $statusUrl = \yii\helpers\Url::to(['ajax-status']);
    $this->registerJs(<<<JS
    var currentCheckbox = null;
    var statusList = document.querySelectorAll('.change-status-checkbox');
    var statusSwitchery = null;
    var modal = $('#up-down-modal');
    for(var i = 0; i < statusList.length; i++)
    {
        statusSwitchery = new Switchery(statusList[i], {"size":"small","className":"switchery"});
        (function (checkbox){
            $(checkbox.element).click(function(){
                var status = checkbox.isChecked() ? 1 : 0;
                if(status === 0)
                {
                    modal.find('.modal-body').text('确定禁用吗？');
                }
                else
                {
                    modal.find('.modal-body').text('确定启用吗？');
                }
                modal.modal('show');
                currentCheckbox = checkbox;
                return false;
            });
        })(statusSwitchery);
    }
    
    modal.find('.sure-btn').click(function(){
        changeStatus(currentCheckbox);
    });
    
    function changeStatus(checkbox)
    {
        var status = checkbox.isChecked() ? 1 : 0;
        $.post('{$statusUrl}', {status: status, call_center_id: $(checkbox.element).attr('data-id')}, function(rs){
            if(rs.status === 200)
            {
                checkbox.setPosition(true);
                checkbox.handleChange();
                modal.modal('hide');
            }
            else 
            {
                modal.find('.warning-active').empty().text(rs.message);
            }
        }, 'json');
    }
JS
    );?>
</div>
<!--启用end-->

<?php
$updateUrl = \yii\helpers\Url::to(['update', 'id' => '__id__']);
$createUrl = \yii\helpers\Url::to(['create']);
$this->registerJs(<<<JS
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
    });
    
    $('.add-Carousel').on('click',function(){
        $('#featured-form').trigger('reset.yiiActiveForm');
        $('.modal form').attr('action', '{$createUrl}');
        $('.input_box input').val('');
        $('#call-center-name').removeAttr('readOnly');
        $('#call-center-name').val('');
    });
JS
);?>

