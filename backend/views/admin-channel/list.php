<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use yii\bootstrap\Html;
use yii\helpers\Url;

$this->title = 'CRM客户来源渠道管理';

$this->params['breadcrumbs'] = [$this->title];

/** @var \yii\data\DataProviderInterface $provider */
/** @var \common\models\Source[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
\toxor88\switchery\SwitcheryAsset::register($this);
\backend\assets\SortAsset::register($this);
?>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>CRM客户来源渠道管理 </h5>
                    <div class="ibox-tools">
                        <?php if (Yii::$app->user->can('customer-source/list')): ?>
                            <a href="#" class="btn btn-primary btn-sm add-source-modal" data-target="#add-source-modal" data-toggle="modal"><span class="fa fa-plus"></span> 新增</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ibox-content">
                    <ul class="list-group sortablelist">
                        <li class="list-group-item">
                            <div class="row">
                                <span class="col-xs-2">客户来源ID</span>
                                <span class="col-xs-4">客户来源名称</span>
                                <span class="col-xs-1">生效</span>
                                <span class="text-right col-xs-2">排序</span>
                                <span class="text-right col-xs-3">操作</span>
                            </div>
                        </li>
                        <?php foreach ($models as $model):
                            $options = [
                                'id' => false,
                                'class' => 'change-status-checkbox',
                                'label' => false,
                                'data-id' => $model->id,
                            ];
                            if (!Yii::$app->user->can('customer-source/list')) {
                                $options['readonly'] = 'readonly';
                            }
                            ?>
                            <li class="list-group-item so1 sortableitem" data-id="<?= $model->id ?>">
                                <div class="row">
                                    <div class="col-xs-2">
                                        <span><?= $model->id; ?></span>
                                    </div>
                                    <div class="col-xs-4">
                                        <?= $model->name; ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <label>
                                            <?= Html::activeCheckbox($model, 'status', $options); ?>
                                        </label>
                                    </div>
                                    <div class="text-right col-xs-2" data-id="<?= $model->id ?>">
                                                <span class="btn btn-xs btn-link move-up"><i
                                                            class="glyphicon glyphicon-arrow-up"></i></span>
                                        <span class="btn btn-xs btn-link move-down"><i
                                                    class="glyphicon glyphicon-arrow-down"></i></span>
                                    </div>
                                    <div class="text-right col-xs-3">
                                            <span class="btn btn-xs btn-white delete-btn" data-target="#delete-source-modal"
                                                  data-toggle="modal" data-id="<?= $model->id; ?>" data-name="<?= $model->name; ?>">删除</span>
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



<?php if (Yii::$app->user->can('customer-source/list')): ?>
    <div class="modal fade" id="add-source-modal" role="dialog" aria-labelledby="modal-title">
        <?php
        $model = new \common\models\Channel();
        $model->loadDefaultValues();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['create'],
            'validationUrl' => ['create', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'id' => 'customer-source-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-4',
                    'offset' => 'col-sm-offset-2',
                    'wrapper' => 'col-sm-6',
                ],
            ],
        ]); ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">CRM来源渠道新增</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($model, 'name')->textInput() ?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
        <?php $this->registerJs(<<<JS
        $('.add-source-modal').click(function() {
            var form = $('#customer-source-form');
            form.find('.warning-active').text('');
            form.trigger('reset.yiiActiveForm');
        });
        $('#customer-source-form').on('beforeSubmit', function(){
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
)?>
    </div>

    <div class="modal fade" id="up-down-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">确认操作</h4>
                </div>
                <div class="modal-body">
                    确定禁用吗?
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary sure-status-btn">确定</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete-source-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">客户来源删除</h4>
                </div>
                <div class="modal-body delete-source">

                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary sure-btn">确定</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    $statusUrl = Url::to(['admin-channel/status']);
    $deleteUrl = Url::to(['admin-channel/delete']);
    $sortUrl = Url::to(['admin-channel/sort']);
    $this->registerJs(<<<JS
    var currentCheckbox = null;
    var statusList = document.querySelectorAll('.change-status-checkbox');
    var statusSwitchery = null;
    var modal = $('#up-down-modal');
    for(var i = 0; i < statusList.length; i++)
    {
        statusSwitchery = new Switchery(statusList[i], {"size":"small", "className":"switchery"});
        (function (checkbox){
            $(checkbox.element).unbind();
            $(checkbox.element).click(function(){
                var status = checkbox.isChecked() ? 0 : 1;
                if(status === 0)
                {
                    modal.find('.modal-body').text('确定启用吗？');
                }
                else
                {
                    modal.find('.modal-body').text('确定禁用吗？');
                }
                modal.modal('show');
                currentCheckbox = checkbox;
                return false;
            });
        })(statusSwitchery);
    }
    
    modal.find('.sure-status-btn').click(function(){
        changeStatus(currentCheckbox);
    });
    
    function changeStatus(checkbox)
    {
        var status = checkbox.isChecked() ? 0 : 1;
        $.post('{$statusUrl}', {status: status, id: $(checkbox.element).attr('data-id')}, function(rs){
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
    
     $('.sortablelist').clickSort({
            speed:200,
            moveCallback: function(source_id, target_id){
            $.post('{$sortUrl}', {source_id: source_id, target_id: target_id}, function(rs){
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
    
    $('.delete-btn').unbind();
    $('.delete-btn').on('click',function(){
        $('#delete-source-modal .warning-active').html('');
	    var delete_id = $(this).attr('data-id');
	    var name = $(this).attr('data-name');
	    $('#delete-source-modal .delete-source').html('确定要删除"'+delete_id+'-'+name+'"吗？');
	    $('.sure-btn').unbind();
	    $('.sure-btn').on('click',function(){
	        $.post('{$deleteUrl}',{id:delete_id},function(rs){
	        if(rs.status != 200){
	            $('#delete-source-modal .warning-active').text(rs.message);
	        }else{
	            window.location.reload();
	        }
	    },'json');
	   	    return false;
	    });
	});
JS
    );?>
<?php else: ?>
    <?php
    $this->registerJs(<<<JS
    var statusList = document.querySelectorAll('.change-status-checkbox');
    for(var i = 0; i < statusList.length; i++)
    {
        new Switchery(statusList[i], {"size":"small","className":"switchery"});
    }
JS
    );?>
<?php endif; ?>