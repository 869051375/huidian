<?php
/* @var $this yii\web\View */

use common\models\Administrator;
use imxiangli\select2\Select2Widget;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var Administrator $administrator */
$administrator = Yii::$app->user->identity;
/** @var Administrator $model */
$this->title = '离职人员确认';
$this->params['breadcrumbs'] = [$this->title];
$companyOptions = ['class' => 'form-control', 'prompt'=>'请选择公司'];
$departmentOptions = ['class' => 'form-control', 'prompt'=>'请选择部门'];
?>
<div class="tabs-container">
	<div class="row" style="margin: 0;">
		<div class="ibox-title text-right" style="border-bottom: 3px solid #e7eaec;padding: 14px 15px;">
			<a  href="<?= Url::to(['administrator/dimission']) ?>" class="btn btn-primary">离职人员列表</a>
		</div>
	</div>
    <div class="tab-content" style="background: #fff;">
    	<div class="clearfix" style="width:1150px;padding-bottom:120px;margin: 0 auto;">
		    <div class="pull-left">
		        <div class="float-e-margins">
		        	<p class="dimission-title">离职人员</p>
		        	<div class="dimission-top">
		        		<h2 class="font-bold no-margins"><?= $model->name; ?></h2>
		        		<div>
		                    <img src="<?= $model->getImageUrl(100, 100) ?>" class="img-circle" alt="profile">
		                </div>
		                <p><?= $model->getTypeName(); ?></p>
		        	</div>
		        	<div class="dimission-bottom dimission-old">
		        		<?php if($model->isCompany()): ?>
		                <p><?= $model->company ? $model->company->name.'-' : null; ?><?= $model->department ? $model->department->name : '--'; ?></p>
		                <?php endif; ?>
		                <p><?= $model->title ? $model->title : '--'; ?></p><br>
		                <p>
		                    <?php
		                    $auth = Yii::$app->authManager;
		                    $hasRoles = $auth->getRolesByUser($model->id);
		                    foreach($hasRoles as $hasRole):?>
		                        <?= $hasRole->description; ?>
		                    <?php endforeach; ?>
		                </p>
		        	</div>
		        </div>
		    </div>
		    <div class="pull-left dimission-icon">
		         <span></span>
		    </div>
		    <div class="pull-left">
		        <div class="float-e-margins">
		        	<p class="dimission-title">接手人员</p>
		        	<div class="dimission-top">
		                <h2 class="font-bold no-margins take-name">--</h2>
		                <div>
		                    <img src="<?= Yii::$app->request->getBaseUrl().'/images/default.png' ?>" class="img-circle take-img" alt="profile">
		                </div>
		                <p class="type-name">--</p>
                   	</div>
                   	<div class="dimission-bottom dimission-new">
                   		<div class="page-select2-area">
		                    <?php
		                    $changeCompanyUrl = Url::to(['administrator/ajax-list', 'type' => $model->type, 'department_id' => '__department_id__','administrator_id' => $model->id]);
		                    $detailUrl = Url::to(['administrator/ajax-detail']);
		                    $leaveTakeOverForm = new \backend\models\DimissionForm();
		                    $form = \yii\bootstrap\ActiveForm::begin([
		                        'id' => 'administrator-form',
		                        'action' => ['administrator/leave','id' => $model->id],
		                        'layout' => 'horizontal',
		                        'fieldConfig' => [
		                            'horizontalCssClasses' => [
		                                'label' => 'col-sm-2 dimission-name',
		                                'offset' => 'col-sm-offset-2',
		                                'wrapper' => 'col-sm-8 dimission-box',
		                            ],
		                        ],
		                    ]); ?>
		                    <?= $form->field($leaveTakeOverForm, 'company_id')->widget(\imxiangli\select2\Select2Widget::className(), [
		                        'serverUrl' => \yii\helpers\Url::to(['company/ajax-list', 'company_id' => $administrator->company_id ? $administrator->company_id : null]),
		                        'itemsName' => 'company',
		                        'placeholderId' => '0',
		                        'width' => '312px',
		                        'placeholder' => '请选择公司',
		                        'searchKeywordName' => 'keyword',
		                        'eventSelect' => new JsExpression("
		                               $('#dimissionform-department_id').val('0').trigger('change');
		                                ")
		                    ])->label('所属公司');
		                    $companyUrl = \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']);
		                    echo $form->field($leaveTakeOverForm, 'department_id')->widget(Select2Widget::className(), [
		                        'options' => $departmentOptions,
		                        'width' => '312px',
		                        'placeholder' => '请选择部门',
		                        'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']),
		                        'itemsName' => 'department',
		                        'eventOpening' => new JsExpression("
		                                var id = $('#dimissionform-company_id').val();
		                                serverUrl = '{$companyUrl}'.replace('__company_id__', id ? id : '-1');
		                    ")
		                    ])->label('所属部门');?>
		                    <?= $form->field($leaveTakeOverForm, 'take_administrator_id')->widget(Select2Widget::className(), [
		                        'nameField' => 'name',
		                        'placeholder' => '请选择接收人',
		                        'searchKeywordName' => 'keyword',
		                        'width' => '312px',
		                        'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-list', 'type' => \common\models\Administrator::TYPE_SALESMAN, 'department_id' => '__department_id__','administrator_id' => $model->id]),
		                        'itemsName' => 'items',
		                        'eventOpening' => new JsExpression("
		                            var id = $('#dimissionform-department_id').val();
		                            serverUrl = '{$changeCompanyUrl}'.replace('__department_id__', id ? id : '-1');
		                        "),
		                        'eventSelect' => new JsExpression("
		                            $(function(){
		                            var id = $('#dimissionform-take_administrator_id').val();
		                                $.get('{$detailUrl}',{id:id},function(rs)
		                                {
		                                    $('.take-name').text(rs.model.name)
		                                    $('.type-name').text(rs.model.typeName)
		                                    $('.take-img').attr('src',rs.model.imageUrl)
		                                },'json')
		                            })
		                        ")
		                    ])->label('接手人员');?>
		                    <?= \yii\helpers\Html::activeHiddenInput($leaveTakeOverForm,'administrator_id',['value' => $model->id]) ?>
		                    <div class="text-center">
		                        <button type="button" class="btn btn-danger leave-btn">离职</button>
		                    </div>
		                    <div class="modal fade" id="btn_carousel" role="dialog" aria-labelledby="myModalLabel">
		                        <div class="modal-dialog" role="document">
		                            <div class="modal-content">
		                                <div class="modal-header">
		                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
		                                                aria-hidden="true">&times;</span></button>
		                                    <h4 class="modal-title" id="myModalLabel">人员离职确认</h4>
		                                </div>
		                                <div class="modal-body">当前人员确定要离职么？人员离职后，交接信息不可逆转。</div>
		                                <div class="modal-footer">
		                                    <span class="text-danger warning-active"></span>
		                                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
		                                    <button type="submit" class="btn btn-primary sure-btn">确定</button>
		                                </div>
		                            </div>
		                        </div>
		                    </div>
		                    <?php \yii\bootstrap\ActiveForm::end(); ?>
		                </div>
                   	</div>
		        </div>
		    </div>
		</div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    $('.leave-btn').click(function() 
    {
        if($('#dimissionform-company_id').val() < 1)
        {
            $('#dimissionform-company_id').blur();
            return false;
        }
        if($('#dimissionform-department_id').val() < 1)
        {
            $('#dimissionform-department_id').blur();
            return false;
        }
        if($('#dimissionform-take_administrator_id').val()  < 1)
        {
            $('#dimissionform-take_administrator_id').blur();
            return false;
        }
        $('#btn_carousel').modal('show');
    })
JS
);
?>