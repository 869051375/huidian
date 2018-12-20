<?php
/** @var \yii\web\View $this */

use common\models\RewardProportion;
use imxiangli\select2\Select2Widget;
use yii\web\JsExpression;

/** @var \common\models\CrmDepartment[] $departments */
/** @var int $id */
/** @var int $child_id */
/** @var int $company_id */
$this->title = '部门管理';
$this->params['breadcrumbs'] = [
    ['label' => '组织机构', 'url' => ['company/list']],
    $this->title
];
?>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-xs-3">
                            <strong>维护部门机构</strong>
                        </div>
                        <div class="col-xs-3 col-xs-offset-1">
                            <strong>部门</strong>
                        </div>
                        <div class="col-xs-3 col-xs-offset-1">
                            <strong>子部门</strong>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row">

                        <div class="col-xs-3">
                            <ul class="list-group sortablelist">
                                <?php
                                $currentParent = null;
                                $children = [];
                                foreach ($departments as $department):
                                    if (empty($currentParent) && ($id == 0 || $id == $department->id)) {
                                        $currentParent = $department;
                                        $children = $department->children;
                                    }
                                    ?>
                                    <?php if ($department->level == 1):?>
                                    <li class="list-group-item sortableitem so1 <?= $currentParent && $currentParent->id == $department->id && $currentParent->level == $department->level? 'list-group-item-info' : '' ?>"
                                        data-sort=" " data-id="<?= $department->id ?>" data-company-id="<?= $company_id ?>">
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <a class="color_66"
                                                   href="<?= \yii\helpers\Url::to(['list', 'id' => $department->id, 'company_id' => $company_id]) ?>"><?= $department->name ?></a>
                                            </div>
                                            <div class="col-xs-6">
                                                <?php if (Yii::$app->user->can('department/update')): ?>
                                                    <a class="btn btn-xs btn-white pull-right update-btn"
                                                       data-toggle="modal" data-target="#myModal" href="#"
                                                       data-whatever="编辑一级部门">编辑</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('department/delete')): ?>
                                                    <a class="btn btn-xs btn-white pull-right delete-btn"
                                                       data-toggle="modal" data-target="#myModal2" href="#">删除</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endif;?>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (Yii::$app->user->can('department/create')): ?>
                                <span class="btn btn-default btn-block add-btn" data-toggle="modal"
                                      data-target="#myModal"
                                      data-whatever="新增一级部门" data-pid="0" data-company-id="<?= $company_id?>">新增</span>
                            <?php endif; ?>
                        </div>

                        <div class="col-xs-1">
                            <span class="glyphicon glyphicon-chevron-right text-center center-block"
                                  style="font-size: 24px;color: #1ab394;margin-top: 60px;"></span>
                        </div>

                        <div class="col-xs-3">
                            <ul class="list-group sortablelist">
                            <?php
                            $childrenDepartment = null;
                            $grandsonDepartment = [];
                            foreach ($children as $child):
                                if (empty($childrenDepartment) && ($child_id == 0 || $child_id == $child->id)) {
                                    $childrenDepartment = $child;
                                    $grandsonDepartment = $child->children;
                                }
                                 ?>
                                <?php if ($child->level == 2):?>
                                    <li class="list-group-item hover_li sortableitem so2 <?= /** @var \common\models\CrmDepartment $childrenDepartment */
                                    $childrenDepartment && $childrenDepartment->id == $child->id ? 'list-group-item-info' : '' ?>"
                                        data-sort=" " data-id="<?= $child->id ?>" data-company-id="<?= $company_id ?>">
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <a class="color_66" href="<?= \yii\helpers\Url::to(['list', 'id' => $currentParent->id, 'child_id' => $child->id, 'company_id' => $company_id]) ?>"><?= $child->name ?></a>
                                            </div>
                                            <div class="col-xs-6">
                                                <?php if (Yii::$app->user->can('department/update')): ?>
                                                    <a class="btn btn-xs btn-white pull-right update-btn"
                                                       data-toggle="modal" data-target="#myModal" data-whatever="编辑二级部门"
                                                       href="#">编辑</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('department/delete')): ?>
                                                    <a class="btn btn-xs btn-white pull-right delete-btn" href="#"
                                                       data-toggle="modal"
                                                       data-target="#myModal2">删除</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endif;?>
                                <?php endforeach; ?>
                            </ul>

                            <?php if ($currentParent): ?>
                                <?php if (Yii::$app->user->can('department/create')): ?>
                                    <span class="btn btn-default btn-block add-btn" data-toggle="modal"
                                          data-target="#myModal"
                                          data-whatever="新增二级部门" data-pid="<?= $currentParent->id ?>" data-company-id="<?= $company_id?>">新增</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="col-xs-1">
                            <span class="glyphicon glyphicon-chevron-right text-center center-block"
                                  style="font-size: 24px;color: #1ab394;margin-top: 60px;"></span>
                        </div>

                        <div class="col-xs-3">
                            <ul class="list-group sortablelist">
                                <?php foreach ($grandsonDepartment as $grandson): ?>
                                    <?php if ($grandson->level == 3):?>
                                    <li class="list-group-item hover_li sortableitem so2"
                                        data-sort="" data-id="<?= $grandson->id ?>" data-company-id="<?= $company_id ?>">
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <a class="color_66" href="#"><?= $grandson->name ?></a>
                                            </div>
                                            <div class="col-xs-6">
                                                <?php if (Yii::$app->user->can('department/update')): ?>
                                                    <a class="btn btn-xs btn-white pull-right update-btn"
                                                       data-toggle="modal" data-target="#myModal" data-whatever="编辑三级部门"
                                                       href="#">编辑</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('department/delete')): ?>
                                                    <a class="btn btn-xs btn-white pull-right delete-btn" href="#"
                                                       data-toggle="modal"
                                                       data-target="#myModal2">删除</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endif;?>
                                <?php endforeach; ?>
                            </ul>

                            <?php if ($childrenDepartment): ?>
                                <?php if (Yii::$app->user->can('department/create')): ?>
                                    <span class="btn btn-default btn-block add-btn" data-toggle="modal"
                                          data-target="#myModal"
                                          data-whatever="新增三级部门" data-pid="<?= $childrenDepartment->id ?>" data-company-id="<?= $company_id?>">新增</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="myModal" role="dialog" aria-labelledby="myModalLabel" data-id="">
        <?php
        $model = new \common\models\CrmDepartment();
        $model->parent_id = $id;
        $model->company_id = $company_id;
        $ajaxLeaderListUrl = \yii\helpers\Url::to(['administrator/ajax-list', 'department_id' => '__id__']);
        $proportionUrl = \yii\helpers\Url::to(['reward-proportion/proportion']);
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['crm-department/create'],
            'enableAjaxValidation' => true,
            'validationUrl' => ['crm-department/create'],
            'id' => 'crm-department-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-3',
                    'offset' => 'col-sm-offset-3',
                    'wrapper' => 'col-sm-8',
                    'hint' => 'col-sm-offset-3 col-sm-8'
                ],
            ],
        ]); ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">新增部门</h4>
                </div>
                <div class="spiner-example loading" style="display: block">
                    <div class="sk-spinner sk-spinner-three-bounce">
                        <div class="sk-bounce1"></div>
                        <div class="sk-bounce2"></div>
                        <div class="sk-bounce3"></div>
                    </div>
                </div>
                <div class="modal-body input_box" style="display: none">
                    <?= \yii\bootstrap\Html::activeHiddenInput($model, 'parent_id', ['id' => 'parent_id']);?>
                    <?= \yii\bootstrap\Html::activeHiddenInput($model, 'company_id', ['id' => 'company_id']);?>
                    <?= $form->field($model, 'name')->textInput() ?>
                    <?= $form->field($model, 'code')->textInput() ?>
                    <?= $form->field($model, 'leader_id')->widget(Select2Widget::className(),[
                        'attribute' => 'leader_id',
                        'serverUrl' => $ajaxLeaderListUrl,
                        'itemsName' => 'items',
                        'options' => ['prompt'=>'选择部门负责人'],
                        'placeholder' => '选择部门负责人',
                        'width' => '200px',
                        'eventOpening' => new JsExpression("
                        var id = $('#myModal').attr('data-id');
                        serverUrl = '{$ajaxLeaderListUrl}'.replace('__id__', id);
                    ")
                    ]) ?>
                    <?= $form->field($model, 'assign_administrator_id')->widget(Select2Widget::className(),[
                        'attribute' => 'assign_administrator_id',
                        'serverUrl' => $ajaxLeaderListUrl,
                        'itemsName' => 'items',
                        'options' => ['prompt'=>'商机默认分配'],
                        'placeholder' => '商机默认分配',
                        'width' => '200px',
                        'eventOpening' => new JsExpression("
                        var id = $('#myModal').attr('data-id');
                        serverUrl = '{$ajaxLeaderListUrl}'.replace('__id__', id);
                    ")
                    ]) ?>
                    <?= $form->field($model, 'reward_proportion_id')->widget(Select2Widget::className(),[
                        'attribute' => 'reward_proportion_id',
                        'serverUrl' => $proportionUrl,
                        'itemsName' => 'proportion',
                        'options' => ['prompt'=>'请选择提成方案'],
                        'placeholder' => '请选择提成方案',
                        'width' => '200px',
                        'eventOpening' => new JsExpression("
                        serverUrl = '{$proportionUrl}';
                    ")
                    ]) ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            部门领导/助理
                        </label>
                        <div class="col-sm-8">
                            <div class="form-control-static">
                                <div class="row department-managers">

                                </div>
                            </div>
                        </div>
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

    <div class="modal fade" id="myModal2" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">删除部门</h4>
                </div>
                <div class="modal-body">
                    确定删除此部门吗?
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
$productCategoryTemplate = '<div class="col-sm-3">{name}</div>';
$departmentCreateUrl = \yii\helpers\Url::to(['create']);
$departmentUpdateUrl = \yii\helpers\Url::to(['update', 'id' => '__id__', 'company_id' => '__company_id__']);
$departmentDeleteUrl = \yii\helpers\Url::to(['delete']);
$departmentDetailUrl = \yii\helpers\Url::to(['detail', 'id' => '__id__']);
$this->registerJs(<<<JS
	
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
    $('#crm-department-form').on('beforeSubmit', function(){
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
	        $.post('{$departmentDeleteUrl}',{id:delete_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	            window.location.reload();
	        }
	    },'json')
	    })
	});
	
	$('#show_nav').click(function(){
        if($(this).is(':checked')){
            $(this).val(1);  
        }
    });
    
    var createAction = '{$departmentCreateUrl}';
    var updateAction = '{$departmentUpdateUrl}';
    $('.add-btn').on('click',function(){
        $('#myModal').attr('data-id','-1');
        $('#crm-department-form').trigger('reset.yiiActiveForm');
        $('#crmdepartment-leader_id').html('');
        $('#crmdepartment-assign_administrator_id').html('');
        $('#crmdepartment-reward_proportion_id').html('');
        $('.department-managers').empty();
        $('.product-category').html('');
        $('.loading').hide();
        $('.modal-body').show();
        $('.modal form').attr('action', createAction);
        var parent_id = $(this).attr('data-pid');
        var company_id = $(this).attr('data-company-id');
        // $('#crm-department-form').trigger('reset.yiiActiveForm');
        $('#image').html('');
        $('#parent_id').val(parent_id);
        if(parent_id==0)
        {
            $('#file-content').css('display','none');
            $('#file-content-banner').css('display','block');
        }
        else
        {
            $('#file-content').css('display','block');
            $('#file-content-banner').css('display','none');
        }
    });
    
    $('.update-btn').on('click',function(){
        var productCategoryTemplate = '{$productCategoryTemplate}';
        $('#crmdepartment-leader_id').html('');
        $('#crmdepartment-assign_administrator_id').html('');
        $('#crmdepartment-reward_proportion_id').html('');
        // $('.product-category').html('');
        $('.department-managers').empty();
        var id = $(this).parents('.list-group-item').attr('data-id');
        var company_id = $(this).parents('.list-group-item').attr('data-company-id');
        $('.modal form').attr('action', updateAction.replace('__id__', id).replace('__company_id__', company_id));
        
        $.get('{$departmentDetailUrl}'.replace('__id__', id),function(rs){
            if(rs.status!=200){
            }else{
                $('.loading').hide();
                $('.modal-body').show();
                $('#parent_id').val(rs.model.parent_id);
                if(rs.model.parent_id==0)
                {
                    $('#file-content').css('display','none');
                    $('#file-content-banner').css('display','block');
                }
                else
                {
                    $('#file-content').css('display','block');
                    $('#file-content-banner').css('display','none');
                }
                $('#crmdepartment-name').val(rs.model.name);
                $('#crmdepartment-code').val(rs.model.code);
                $('#myModal').attr('data-id', rs.model.id);

                for(var i in rs.departmentManagers)
                {
                    var item = productCategoryTemplate.replace('{name}', rs.departmentManagers[i].name);
                    $('.department-managers').append(item);
                }
                if(rs.leader)
                {
                    $('#crmdepartment-leader_id').html('<option value="'+ rs.leader.id+ '">'+ rs.leader.name +'</option>');
                }
                if(rs.assign_administrator)
                {
                    $('#crmdepartment-assign_administrator_id').html('<option value="'+ rs.assign_administrator.id+ '">'+ rs.assign_administrator.name +'</option>');
                }
                if(rs.rewardProportion)
                {
                    $('#crmdepartment-reward_proportion_id').html('<option value="'+ rs.rewardProportion.id+ '">'+ rs.rewardProportion.name +'</option>');
                }
                // $('select').val(rs.leaders.name);
                $('select').trigger('changer.select2');
            }
        }, 'json');
    })
JS
);
?>