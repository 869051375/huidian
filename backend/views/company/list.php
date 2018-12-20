<?php

use backend\widgets\LinkPager;
use common\models\Administrator;
use imxiangli\select2\Select2Widget;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $provider yii\data\ActiveDataProvider */

$this->title = '组织机构';
$this->params['breadcrumbs'][] = $this->title;
$models = $provider->getModels();
$pagination = $provider->getPagination();
?>
    <div class="row">
        <div class="col-xs-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>公司列表 </h5>
                    <?php if (Yii::$app->user->can('company/create')): ?>
                        <div class="ibox-tools">
                            <a href="#" class="btn btn-primary btn-sm add-company" data-target="#addModel"
                               data-toggle="modal"><span class="fa fa-plus"></span> 新建公司</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ibox-content">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th class="col-md-1">公司ID</th>
                            <th class='col-md-4'>公司名称</th>
                            <th class="col-md-1 text-center">部门数量</th>
                            <th class="col-md-2 text-center">创建时间</th>
                            <th class="col-md-2 text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php /** @var \common\models\Company $model */
                        foreach ($models as $model): ?>
                            <tr data-id="<?= $model->id ?>">
                                <td><?= $model->id ?></td>
                                <td>
                                    <a href="<?= \yii\helpers\Url::to(['crm-department/list', 'company_id' => $model->id]) ?>"><?= $model->name ?></a>
                                </td>
                                <td class="text-center"><?= $model->countDepartment();?></td>
                                <td class="text-center"><?= \Yii::$app->formatter->asDatetime($model->created_at)?></td>
                                <td class="text-center">
                                    <?php if (Yii::$app->user->can('company/delete')): ?>
                                        <span class="btn btn-xs btn-white delete-btn" data-target="#deleteModel"
                                              data-toggle="modal" data-id="<?= $model->id ?>">删除</span>
                                    <?php endif; ?>
                                    <?php if (Yii::$app->user->can('company/update')): ?>
                                        <span class="btn btn-xs btn-white update-btn" data-target="#addModel"
                                              data-toggle="modal" data-whatever="编辑公司">修改</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="10">
                                <?= LinkPager::widget(['pagination' => $pagination]); ?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addModel" role="dialog" aria-labelledby="modal-title">
        <?php
        $model = new \common\models\Company();
        $model->loadDefaultValues();
        $administratorUrl = \yii\helpers\Url::to(['administrator/ajax-list', 'company_id' => '__company_id__', 'type' => Administrator::TYPE_ADMIN]);
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['company/create'],
            'validationUrl' => ['company/validation'],
            'enableAjaxValidation' => true,
            'id' => 'link-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-3',
                    'offset' => 'col-sm-offset-1',
                    'wrapper' => 'col-sm-4',
                    'hint' => 'col-md-5',
                ],
            ],
        ]); ?>
        <div id="company-id" class="modal-dialog" role="document" data-id="">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">新建公司</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                    <div id="is-show-financial">
                        <?= $form->field($model, 'financial_id')->widget(Select2Widget::className(), [
                            'model' => $model,
                            'attribute' => 'department_id',
//                        'selectedItem' => $model->oneAdministrator ? [$model->oneAdministrator->id => $model->administrator->name] : [],
                            'options' => ['class' => 'form-control', 'prompt'=>'请选择财务提醒人'],
                            'placeholderId' => '0',
                            'placeholder' => '请选择财务提醒人',
                            'width' => '160px',
                            'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-list', 'company_id' => '__company_id__', 'type' => Administrator::TYPE_ADMIN]),
                            'itemsName' => 'items',
                            'eventOpening' => new JsExpression("
                                var id = $('#company-id').attr('data-id');
                                serverUrl = '{$administratorUrl}'.replace('__company_id__', id ? id : '-1');
                            ")
                        ]);?>
                    </div>
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

    <div class="modal fade" id="deleteModel" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">删除公司</h4>
                </div>
                <div class="modal-body">
                    确认要删除当前公司吗？
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
$deleteUrl = \yii\helpers\Url::to(['delete']);
$createUrl = \yii\helpers\Url::to(['create']);
$updateUrl = \yii\helpers\Url::to(['update', 'id' => '__id__']);
$detailUrl = \yii\helpers\Url::to(['detail', 'id' => '__id__']);
$this->registerJs(<<<JS
    $('#addModel').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');
        var modal = $(this);
        modal.find('.modal-title').text(recipient);
    });
    $('.cancel-btn').on('click',function(){
        $('.warning-active').html('');
    });
	$('.delete-btn').on('click',function(){
	    var delete_id = $(this).attr('data-id');
	    $('.sure-btn').on('click',function(){
	        $.post('{$deleteUrl}',{id:delete_id},function(rs){
                if(rs.status != 200){
                    $('.warning-active').html(rs.message);
                }else{
                    window.location.reload();
                }
            },'json');
	    });
	});
    $('.add-company').on('click',function(){
        $('#is-show-financial').css('display', 'none');
        $('.modal form').attr('action', '{$createUrl}');
        $('.input_box input').val('');
        $('#image').empty();
        $('#link-form').trigger('reset.yiiActiveForm');
    });   
    $('.update-btn').on('click',function(){
        $('#is-show-financial').css('display', 'block');
        $('#link-form').trigger('reset.yiiActiveForm');
        var id = $(this).parents('tr').attr('data-id');
        var updateAction = '{$updateUrl}';
        $('.modal form').attr('action', updateAction.replace('__id__', id));
        $.get('{$detailUrl}'.replace('__id__', id), function(rs){
            if(rs.status!=200){
                
            }else{
                $('#company-id').attr('data-id', rs.model.id);
                $('#company-name').val(rs.model.name);
                $('#company-financial_id').val(rs.model.financial_id);
                var financial_name = rs.model.financial_name ? rs.model.financial_name : '<span class="select2-selection__placeholder">请选择财务提醒人</span>';
                $('#select2-company-financial_id-container').html(financial_name);
            }
        },'json');
    }); 
JS
);
?>