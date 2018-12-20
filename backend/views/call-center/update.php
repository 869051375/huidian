<?php

/* @var $this yii\web\View */

use imxiangli\select2\Select2Widget;
use yii\helpers\Html;
use yii\web\JsExpression;

$this->title = $model ? $model->name : '';
$this->params['breadcrumbs'] = [['label' => '外呼集成设置', 'url' => ['/call-center/list']], $this->title];
$imageStorage = Yii::$app->get('imageStorage');

?>
<div id="self-danger-tip" class="alert alert-success hide">
    <a href="#" id="tipClose">&times;</a><span></span>
    <?php $this->registerJs(<<<JS
    $('#tipClose').click(function(){
        $('#self-danger-tip').addClass('hide').removeClass('show');
    });
JS
    );?>
</div>
<div class="row page-select2-area">
    <div class="col-xs-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>【<?= $model->name ?>】</h5>
            </div>
            <div class="ibox-content">
                <!--内容部分start-->
                <?php
                $form = \yii\bootstrap\ActiveForm::begin([
                    'id' => 'customer-create-form',
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label' => 'col-md-2',
                            'offset' => 'col-md-offset-1',
                            'wrapper' => 'col-md-8',
                            'hint' => 'col-md-1',
                        ],
                    ],
                ]); ?>
                <?= $form->field($model, 'name')->textInput(['placeholder'=>'请输入描述名称']);?>
                <?= $form->field($model, 'url')->textInput(['placeholder'=>'请输入对接地址']);?>
                <div class="form-group" style="margin-top: 30px;">
                    <span class="col-md-1 text-right" style="color: #333;margin-left: 92px;">所属公司</span>
                    <div class="col-md-8">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">所属公司</th>
                                <th class="text-center">操作</th>
                            </tr>
                            </thead>
                            <tbody id="opportunity-items">
                            <?php /** @var \common\models\CallCenterAssignCompany[] $callCenterAssignCompany */
                                if ($callCenterAssignCompany):?>
                                    <?php foreach ($callCenterAssignCompany as $company):?>
                                        <tr>
                                            <td style="vertical-align: middle;" class="text-center"><?= $company['id'];?></td>
                                            <td style="vertical-align: middle;" class="text-center"><?= $company['name'];?></td>
                                            <?php if (Yii::$app->user->can('call-center/*')): ?>
                                                <td style="vertical-align: middle;" class="text-center">
                                                    <button type="button" class="btn btn-xs btn-danger delete-btn"
                                                            data-target="#delete-modal" data-toggle="modal" data-id="<?= $company['aid']?>">删除</button>
                                                </td>
                                            <?php endif;?>
                                        </tr>
                                    <?php endforeach;?>
                                <?php endif;?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="3">
                                    <div class="row">
                                        <div class="col-md-6 text-left">
                                            <?php if (Yii::$app->user->can('call-center/*')): ?>
                                                <button type="button" class="btn btn-primary btn-sm add-company-btn" data-target="#add-modal" data-toggle="modal" data-id="<?= $model->id;?>">添加</button>
                                            <?php endif;?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <?= $form->field($model, 'debugging')->textInput(['placeholder'=>'请输入调试参数'])->hint("<button class='btn-xs btn-primary' type='button' id='debugging-btn' data-id='".$model->id."'>调试</button>");?>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <div class="col-sm-12 text-center">
                        <div class="row">
                            <div class="col-xs-12">
                                <?php if (Yii::$app->user->can('call-center/*')): ?>
                                    <button class="main-bg btn btn-primary" type="submit" name="next" value="save">保存</button>
                                <?php endif;?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
                <!--内容部分end-->
            </div>
        </div>
    </div>
</div>
<?php
$url = \yii\helpers\Url::to(['call']);
$this->registerJs(<<<JS
    $('#debugging-btn').click(function() {
        var debugging = $('#callcenter-debugging').val();
        var callUrl = $('#callcenter-url').val();
        var id = $(this).attr('data-id');
        if(debugging == '' || callUrl == '')
        {
            return false;
        }
        else
        {
            $.get('{$url}',{id:id, callUrl:callUrl,debugging:debugging}, function(rs){
                if(rs.status == 200)
                {
                    setGlobalTip('呼叫成功！', false);
                }
                else 
                {
                    setGlobalTip('呼叫失败，请检查地址是否正确！', true);
                }
            }, 'json');
        }
    });
    
    function setGlobalTip(message, isError) {
        if(isError) {
            $('#self-danger-tip span').html(message);
            $('#self-danger-tip').removeClass('hide').addClass('show alert-danger');
        }else{
            $('#self-danger-tip span').html(message);
            $('#self-danger-tip').removeClass('hide alert-danger').addClass('show alert-success');
            setTimeout(function(){
                $('#self-danger-tip').removeClass('show').addClass('hide');
            },5000);
        }
    }
JS
);?>


<!--添加所属公司start-->
<div class="modal fade" id="add-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $model = new \backend\models\CallCenterCompanyForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['call-center-assign-company/ajax-create'],
        'validationUrl' => ['call-center-assign-company/validation'],
        'id' => 'call-center-assign-company-form',
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
                <h4 class="modal-title">新增公司</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($model, 'company_id')->widget(Select2Widget::className(),[
                    'model' => $model,
                    'attribute' => 'company_id',
                    'serverUrl' => \yii\helpers\Url::to(['company/ajax-list', 'call_center' => '1']),
                    'itemsName' => 'company',
                    'selectedItem' => $model->company ? [$model->company->id => $model->company->name] : [],
                    'options' => ['class' => 'form-control', 'prompt'=>'请选择公司'],
                    'placeholderId' => '0',
                    'placeholder' => '请选择公司',
                    'width' => '250px',
                    'eventSelect' => new JsExpression("
                            var id = $(this).val();
                            if(id == '' || id == 0)
                            {
                                $('#callcentercompanyform-company_id').val('');
                            }
                        ")
                ]);?>
            </div>
            <?= Html::activeHiddenInput($model, 'call_center_id') ?>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
            </div>
        </div>
    </div>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>

<div class="modal fade" id="delete-modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">删除公司</h4>
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
$deleteUrl = \yii\helpers\Url::to(['call-center/ajax-company-delete']);
$this->registerJs(<<<JS
    $.fn.select2.defaults.set('width', '100%');
    $('.add-company-btn').click(function(){
        $("#callcentercompanyform-company_id").select2('val','0');//初始化select2
        $('#call-center-assign-company-form').trigger('reset.yiiActiveForm');
        $('.warning-active').html('');
        var id = $(this).attr('data-id');
        $('#callcentercompanyform-call_center_id').val(id);
    });
    
    $('#call-center-assign-company-form').on('beforeSubmit', function(){
        var form = $(this);
        $.post(form.attr('action'), form.serialize(), function(rs){
            if(rs.status === 200)
            {
                var html = '';
                for(var i=0; i<rs.company.length; i++){
                   
                    html += "<tr><td class='text-center'>" + rs.company[i]['id'] + "</td><td class='text-center'>" + rs.company[i]['name'] + "</td><td class='text-center'><button type='button' class='btn btn-xs btn-danger delete-btn' data-target='#delete-modal' data-toggle='modal' data-id='"+ rs.company[i]['aid']+ "'>删除</button></td></tr>";
                }
                $("#opportunity-items").html(html);
                // $('#add-modal').modal('hide');
                window.location.reload();
            }
            else
            {
                form.find('.warning-active').text(rs.message);
            }
        }, 'json');
        return false;
    });
    
    $('.delete-btn').unbind();
	$('.delete-btn').click(function(){
	    var id = $(this).attr('data-id');
	    $('.delete-sure-btn').unbind();
	    $('.delete-sure-btn').click(function(){
	        $.post('{$deleteUrl}',{id: id},function(rs){
	        if(rs.status != 200)
	        {
	            $('.warning-active').html(rs.message);
	        }
	        else
            {
	            window.location.reload();
	        }
	    },'json');
	    });
	});
	
JS
);?>

<!--添加所属公司end-->
