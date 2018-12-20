<?php
/* @var $this yii\web\View */

use common\models\Administrator;
use imxiangli\select2\Select2Widget;
use yii\web\JsExpression;

/** @var Administrator $administrator */
$administrator = Yii::$app->user->identity;
/** @var Administrator $model */
$this->title = '人员调岗';
$this->params['breadcrumbs'] = [$this->title];
?>
<div class="tabs-container">
	<ul class="nav nav-tabs" style="border: none;">
        <li class="active">
            <a style="border: none;border-top: 2px solid #1ab394;">人员调岗管理</a>
        </li>
    </ul>
    <div class="tab-content" style="background: #fff;">
    	<div class="clearfix" style="width:1150px;padding-bottom:120px;margin: 0 auto;">
		    <div class="pull-left">
		        <div class="float-e-margins">
	            	<p class="dimission-title">现岗位</p>
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
		                <p><?= $model->title ? $model->title : '--'; ?></p>
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
		        <div>
	            	<p class="dimission-title">预调岗位</p>
	            	<div class="dimission-top">
		                <h2 class="font-bold no-margins"><?= $model->name; ?></h2>
		                <div>
		                    <img src="<?= $model->getImageUrl(100, 100) ?>" class="img-circle" alt="profile">
		                </div>
                        <p><?= $model->getTypeName(); ?></p>
                   	</div>
                   	<div class="dimission-bottom dimission-new">
		                <div class="page-select2-area">
		                    <?php
		                    $changeJobsForm = new \backend\models\ChangeJobsForm();
		                    $form = \yii\bootstrap\ActiveForm::begin([
		                        'id' => 'administrator-form',
		                        'action' => ['administrator/change-jobs','id' => $model->id],
		                        'layout' => 'horizontal',
		                        'fieldConfig' => [
		                            'horizontalCssClasses' => [
		                                'label' => 'col-sm-2 dimission-name',
		                                'offset' => 'col-sm-offset-2',
		                                'wrapper' => 'col-sm-8 dimission-box',
		                            ],
		                        ],
		                    ]); ?>
		
		                    <?= $form->field($changeJobsForm, 'company_id')->widget(\imxiangli\select2\Select2Widget::className(), [
		                        'selectedItem' => $model->company ? [$model->company->id => $model->company->name] : [],
		                        'serverUrl' => \yii\helpers\Url::to(['company/ajax-list', 'company_id' => $administrator->company_id ? $administrator->company_id : null]),
		                        'itemsName' => 'company',
		                        'placeholderId' => '0',
		                        'width' => '312px',
		                        'placeholder' => '请选择公司',
		                        'searchKeywordName' => 'keyword',
		                        'eventSelect' => new JsExpression("
		                               $('#department_id').val('0').trigger('change');
		                                ")
		                    ])->label('所属公司');
		                    $companyUrl = \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']);
		                    echo $form->field($changeJobsForm, 'department_id')->widget(Select2Widget::className(), [
		                        'selectedItem' => $model->department ? [$model->department->id => $model->department->name] : [],
		                        'width' => '312px',
		                        'placeholder' => '请选择部门',
		                        'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']),
		                        'itemsName' => 'department',
		                        'eventOpening' => new JsExpression("
		                                var id = $('#company_id').val();
		                                serverUrl = '{$companyUrl}'.replace('__company_id__', id ? id : '-1');
		                    ")
		                    ])->label('所属部门');?>
		                    <?= $form->field($changeJobsForm, 'title')->textInput(['value' => $model->title,'placeholder' => '职位名称','style'=>'width:312px']) ?>
		                    <fieldset class="form-horizontal">
		                        <div class="form-group">
		                            <label class="col-sm-2 control-label" style="width:58px;padding: 0;margin-right: 10px;text-align: left;">角色</label>
		                            <div class="col-sm-8 dimission-fiel">
		                                <div class="row">
		                                    <?php
		                                    $auth = Yii::$app->authManager;
		                                    $hasRoles = $auth->getRolesByUser($model->id);
		                                    $roleModel = new \backend\models\AdministratorRole();
		                                    $roles = $roleModel->getAll($model->type);
		                                    foreach ($roles as $role):
		                                        $checked = false;
		                                        foreach ($hasRoles as $hasRole){
		                                            if ($hasRole->name == $role->id){
		                                                $checked = true;
		                                            }
		                                        }
		                                        ?>
		                                        <div class="checkbox col-sm-4">
		                                            <?= \yii\bootstrap\Html::checkbox('role[]', $checked, ['label' => $role->name , 'value' => $role->id]); ?>
		                                        </div>
		                                    <?php endforeach; ?>
		                                </div>
		                            </div>
		                        </div>
		                    </fieldset>
		                    <div class="text-center">
		                        <button type="button" class="btn btn-primary change-btn">调岗</button>
		                    </div>
		                    <div class="modal fade" id="btn_carousel" role="dialog" aria-labelledby="myModalLabel">
		                        <div class="modal-dialog" role="document">
		                            <div class="modal-content">
		                                <div class="modal-header">
		                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
		                                                aria-hidden="true">&times;</span></button>
		                                    <h4 class="modal-title" id="myModalLabel">人员调岗确认</h4>
		                                </div>
		                                <div class="modal-body">
		                                    当前人员确定要调岗么？人员调岗后，交接信息不可逆转。
		                                </div>
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
    $('.change-btn').click(function() 
    {
        if($('#company_id').val() < 1)
        {
            $('#company_id').blur();
            return false;
        }
        if($('#department_id').val() < 1)
        {
            $('#department_id').blur();
            return false;
        }
        $('#btn_carousel').modal('show');
    })
JS
);
?>