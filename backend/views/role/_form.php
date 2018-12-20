<?php
/* @var $this yii\web\View */
use yii\bootstrap\Html;

/* @var $role backend\models\AdministratorRole */
/* @var $form yii\bootstrap\ActiveForm */
$uniqueId = Yii::$app->controller->action->uniqueId;
?>
<?php
$form = \yii\bootstrap\ActiveForm::begin([
    'id' => 'shop-type-form',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'label' => 'col-sm-2',
            'offset' => 'col-sm-offset-2',
            'wrapper' => 'col-sm-8',
            'hint' => 'col-sm-offset-2 col-sm-8',
        ],
    ]]);
?>

<div class="ibox">
    <div class="ibox-content">
        <fieldset class="form-horizontal">
            <?= $form->field($role, 'name')->textInput() ?>
        </fieldset>
        <fieldset class="form-horizontal">
            <?php if($uniqueId == 'role/create'): ?>
                <?= $form->field($role, 'type')->dropDownList(\common\models\Administrator::getTypes()) ?>
            <?php endif;?>
            <?php if($uniqueId == 'role/update'): ?>
                <div class="form-group">
                    <label class="control-label col-sm-2">角色类型</label>
                    <div class="col-sm-8">
                        <div class="form-control"><?= $role->getRoleType() ?></div>
                    </div>
                </div>
            <?php endif;?>
        </fieldset>
        <fieldset class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-2 control-label">
                    权限
                </label>
                <div class="col-sm-8">
                    <div class="row">
                        <?php /** @var array $permissionGroup */
                        $auth = Yii::$app->authManager;
                        $hasPermissions = $auth->getPermissionsByRole($role->id);
                        foreach ($permissionGroup as $key => $permissions):?>
                        <div class="group">
                            <div class="m-t-md text-warning">
                                <?= $permissions['group_name']?>
                            </div>
                            <hr  class="m-t-sm m-b-sm"/>
                            <div class="group-choice">
                                <div class="col-sm-3 group-choice-all">
                                    <?= Html::checkbox("choice-all", false, ['id' => "choice-all", 'class' => 'choice-all', 'label' => '全选']); ?>
                                </div>
                                <?php
                                foreach ($permissions['items'] as $k => $permission):
                                    $checked = false;
                                    foreach ($hasPermissions as $hasPermission) {
                                        if ($hasPermission->name == $k) {
                                            $checked = true;
                                        }
                                    }
                                    ?>
                                <div class="group-items checkbox col-sm-3">
                                    <?= \yii\bootstrap\Html::checkbox('permission[]', $checked, ['class' => 'choice', 'label' => $permission, 'value' => $k]); ?>
                                </div>

                            <?php endforeach;?>
                            <div style="clear:both;"></div>
                            </div>
                        </div>
                        <?php endforeach;?>
                    </div>
                </div>
            </div>
        </fieldset>
        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2">
                <button class="btn btn-primary" type="submit">保存</button>
            </div>
        </div>
    </div>
</div>
<?php \yii\bootstrap\ActiveForm::end(); ?>

<?php $this->registerJs(<<<JS
  
        //全选
        $('.choice-all').click(function(){
        	if ($(this).is(":checked")) {
            	$(this).parents('.group-choice').find('input').prop("checked",true);
            } else {
            	$(this).parents('.group-choice').find('input').prop("checked", false);
            }
        });

        $('.choice').click(function(){
        	var m = $(this).parents('.group-choice').find(".group-items input[type='checkbox']:checked").length;
        	var n = $(this).parents('.group-choice').find(".group-items input").length;
        	if (m < n) {
	        	$(this).parents('.group-choice').find('.group-choice-all input').prop("checked",false);
	        } else {
	        	$(this).parents('.group-choice').find('.group-choice-all input').prop("checked", true);
	        }
        });

JS
);?>
