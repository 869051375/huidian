<?php
/* @var $this yii\web\View */
use common\models\Administrator;
use imxiangli\select2\Select2Widget;
use yii\web\JsExpression;

/** @var Administrator $administrator */
$administrator = Yii::$app->user->identity;
$imageStorage = Yii::$app->get('imageStorage');
$url = '';
/** @var int $type */
if(empty($model->id))
{
    if(empty($type) || $type == Administrator::TYPE_ADMIN)
    {
        $labelName = '管理员管理';
        $this->title = '添加管理员账号';
        $url = ['/administrator/list-manager'];
    }
    elseif ($type == Administrator::TYPE_CUSTOMER_SERVICE)
    {
        $labelName = '客服管理';
        $this->title = '添加客服账号';
        $url = ['/administrator/list-customer-service'];
    }
    elseif ($type == Administrator::TYPE_SUPERVISOR)
    {
        $labelName = '嘟嘟妹管理';
        $this->title = '添加嘟嘟妹账号';
        $url = ['/administrator/list-supervisor'];
    }
    elseif ($type == Administrator::TYPE_CLERK)
    {
        $labelName = '服务人员管理';
        $this->title = '添加服务人员账号';
        $url = ['/administrator/list-clerk'];
    }
    elseif ($type == Administrator::TYPE_SALESMAN)
    {
        $labelName = '业务员管理';
        $this->title = '添加业务员账号';
        $url = ['/administrator/list-salesman'];
    }

    $canSelect = true;
    $companyOptions = ['class' => 'form-control', 'prompt'=>'请选择公司'];
    $departmentOptions = ['class' => 'form-control', 'prompt'=>'请选择部门'];
}
else
{
    if(empty($type) || $type == Administrator::TYPE_ADMIN)
    {
        $labelName = '管理员管理';
        $this->title = '编辑管理员账号';
        $url = ['/administrator/list-manager'];
    }
    elseif ($type == Administrator::TYPE_CUSTOMER_SERVICE)
    {
        $labelName = '客服管理';
        $this->title = '编辑客服账号';
        $url = ['/administrator/list-customer-service'];
    }
    elseif ($type == Administrator::TYPE_SUPERVISOR)
    {
        $labelName = '嘟嘟妹管理';
        $this->title = '编辑嘟嘟妹账号';
        $url = ['/administrator/list-supervisor'];
    }
    elseif ($type == Administrator::TYPE_CLERK)
    {
        $labelName = '服务人员管理';
        $this->title = '编辑服务人员账号';
        $url = ['/administrator/list-clerk'];
    }
    elseif ($type == Administrator::TYPE_SALESMAN)
    {
        $labelName = '业务员管理';
        $this->title = '编辑业务员账号';
        $url = ['/administrator/list-salesman'];
    }

    $canSelect = false;
    $companyOptions = ['class' => 'form-control', 'prompt'=>'请选择公司','disabled'=>'disabled'];
    $departmentOptions = ['class' => 'form-control', 'prompt'=>'请选择部门','disabled'=>'disabled'];
    if(Yii::$app->user->can('company/department-modify'))
    {
        $canSelect = true;
        $companyOptions = ['class' => 'form-control', 'prompt'=>'请选择公司'];
        $departmentOptions = ['class' => 'form-control', 'prompt'=>'请选择部门'];
    }
}
$imageLabel = '头像';
if($type == Administrator::TYPE_CUSTOMER_SERVICE || $type == Administrator::TYPE_SUPERVISOR)
{
    $imageLabel = '头像*';
}
$this->params['breadcrumbs'] = [['label' => $labelName, 'url' => $url], $this->title];

/** @var \common\models\Administrator $model */
?>
<div class="row">
    <div class="col-lg-12">

        <div class="ibox float-e-margins">
            <div class="ibox-content">
                <?php $model->loadDefaultValues(); ?>
                <?php $form = \yii\bootstrap\ActiveForm::begin([
                    'id' => 'administrator-form',
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-2',
                            'offset' => 'col-sm-offset-2',
                            'wrapper' => 'col-sm-8',
                        ],
                    ],
                ]); ?>
                <?php if($type == Administrator::TYPE_SALESMAN && !$model->isNewRecord):?>
                    <?= $form->field($model, 'name')->textInput(['disabled' => 'disabled']) ?>
                <?php else: ?>
                    <?= $form->field($model, 'name')->textInput() ?>
                <?php endif; ?>
                <div class="hr-line-dashed"></div>
                <?= $form->field($model, 'latter')->textInput() ?>
                <div class="hr-line-dashed"></div>
                <?= $form->field($model, 'username')->textInput() ?>
                <div class="hr-line-dashed"></div>
                <?= $form->field($model, 'password')->passwordInput() ?>
                <div class="hr-line-dashed"></div>
                <?= $form->field($model, 'phone')->textInput() ?>
                <div class="hr-line-dashed"></div>
                <?= $form->field($model, 'email')->textInput() ?>
                <div class="hr-line-dashed"></div>
                <?php if ($type == Administrator::TYPE_SALESMAN):?>
                    <?= $form->field($model, 'call_center')->textInput() ?>
                    <div class="hr-line-dashed"></div>
                <?php endif;?>
                <?= $form->field($model, 'is_department_manager')->checkbox() ?>
                <div class="hr-line-dashed"></div>
                <?php $field = $form->field($model, 'image')->hiddenInput(['id'=> 'administrator_image_key'])->label($imageLabel);
                $field->parts['{input}'] = $field->parts['{input}'].\imxiangli\upload\JQFileUpLoadWidget::widget([
                        'buttonTitle' => '上传头像',
                        'name' => 'file',
                        'serverUrl' => ['upload'],
                        'formData' =>[
                            Yii::$app->request->csrfParam => Yii::$app->request->csrfToken,
                        ],
                        'done' => new \yii\web\JsExpression('function (e, data) {
                                            $.each(data.result.files, function (index, file) {
                                            if(file.error)
                                            {
                                                $(".field-administrator_image_key .help-block").html(file.error);
                                            }
                                            else
                                            {
                                                $("#image").empty().append($("<div class=\\"thumbnail pull-left\\"></div>").append($("<img />").attr("src", file.thumbnailUrl)));
                                                $("#administrator_image_key").val(file.key);
                                                $("#administrator_image_key").trigger("blur");                                              
                                            }
                                            });
                                        }')
                    ])
                ?>
                <?= $field ?>
                <div class="form-group">
                    <label class="control-label col-sm-2"></label>
                    <div class="col-sm-8">
                        <div id="image">
                            <?php if ($model->image): ?>
                                <img class="thumbnail margin0"
                                     src="<?= $imageStorage->getImageUrl($model->image, ['width' => 100, 'height' => 100],['class' => 'thumbnail margin0']) ?>"/>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2"></label>
                    <div class="col-sm-8">
                        <div>图片要求，宽高：100px &times; 100px，格式：JPG、PNG</div>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <?php if ($type == Administrator::TYPE_SALESMAN):
                    $model->is_belong_company = 1; ?>
                    <?= $form->field($model, 'is_belong_company')->checkbox(['onclick'=>"return false", 'checked'=>"checked" ]) ?>
                    <div class="form-group">
                        <label class="control-label col-sm-2"></label>
                        <div class="col-sm-8">
                            <div>说明：业务员类型账号必须选择公司与部门！</div>
                        </div>
                    </div>
                <?php else:?>
                    <?php if ($model->id):?>
                        <?php if (Yii::$app->user->can('company/department-modify')):?>
                            <?= $form->field($model, 'is_belong_company')->checkbox() ?>
                        <?php else:?>
                            <?= $form->field($model, 'is_belong_company')->checkbox(['onclick'=>"return false"]) ?>
                        <?php endif;?>
                    <?php else:?>
                        <?= $form->field($model, 'is_belong_company')->checkbox() ?>
                    <?php endif;?>
                <?php endif;?>
                <?php
                $companyField = Select2Widget::widget([
                    'model' => $model,
                    'attribute' => 'company_id',
                    'serverUrl' => \yii\helpers\Url::to(['company/ajax-list', 'company_id' => $administrator->company_id ? $administrator->company_id : null]),
                    'itemsName' => 'company',
                    'selectedItem' => $model->company ? [$model->company->id => $model->company->name] : [],
//                    'options' => ['class' => 'form-control', 'prompt'=>'请选择公司','disabled'=>'disabled'],
                    'options' => $companyOptions,
                    'placeholderId' => '0',
                    'placeholder' => '请选择公司',
                    'width' => '150px',
                    'eventSelect' => new JsExpression("
                                $('#department_id').val('0').trigger('change');
                            ")
                ]);
                ?>
                <?php
                $companyUrl = \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']);
                echo $form->field($model, 'department_id', [
                    'template' => "{label}\n<div class='col-md-4'><div class='row'><div class='col-sm-6'>{$companyField}</div><div class='col-sm-6'>{input}\n{hint}\n{error}</div></div></div>",
                ])->widget(Select2Widget::className(), [
                    'model' => $model,
                    'attribute' => 'department_id',
                    'selectedItem' => \yii\helpers\ArrayHelper::merge(['0' => '请选择部门'], $model->companyDepartment ? [$model->companyDepartment->id => $model->companyDepartment->name] : []),
//                    'options' => ['class' => 'form-control', 'prompt'=>'请选择部门','disabled'=>'disabled'],
                    'options' => $departmentOptions,
                    'placeholderId' => '0',
                    'placeholder' => '请选择部门',
                    'width' => '150px',
                    'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-company-department-list', 'company_id' => '__company_id__']),
                    'itemsName' => 'department',
                    'eventOpening' => new JsExpression("
                                var id = $('#company_id').val();
                                serverUrl = '{$companyUrl}'.replace('__company_id__', id ? id : '-1');
                            ")
                ])
                ?>
                <div class="hr-line-dashed"></div>
                <?= $form->field($model, 'title')->textInput() ?>
<!--                <div class="hr-line-dashed"></div>-->
                <?php if(!empty($model->name)): ?>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="control-label col-sm-2">账号类型</label>
                    <div class="col-sm-8">
                        <div class="form-control"><?= $model->getTypeName() ?></div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="hr-line-dashed"></div>
                <?= $form->field($model, 'status')->checkbox() ?>
                <div class="hr-line-dashed"></div>
                <fieldset class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">
                            角色
                        </label>
                        <div class="col-sm-8">
                            <div class="row">
                                <?php
                                $auth = Yii::$app->authManager;
                                $hasRoles = $auth->getRolesByUser($model->id);
                                $roleModel = new \backend\models\AdministratorRole();
                                $roles = $roleModel->getAll($model->type);
//                                $roles = $auth->getRoles();
                                foreach ($roles as $role):
                                    $checked = false;
                                    foreach ($hasRoles as $hasRole) {
                                        if ($hasRole->name == $role->id) {
                                            $checked = true;
                                        }
                                    }
                                    ?>
                                    <div class="checkbox col-sm-3">
                                        <?= \yii\bootstrap\Html::checkbox('role[]', $checked, ['label' => $role->name , 'value' => $role->id]);
                                        ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-2">
                        <button class="btn btn-primary" type="submit">保存</button>
                    </div>
                </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
            </div>
        </div>

    </div>
</div>
<?php $this->registerJs(<<<JS
    $('#not-limit-category').click(function(){
        if($(this).is(':checked'))
        {
            $('.category').prop('checked', false);
        }
    });
    $('.category').click(function(){
        if($(this).is(':checked'))
        {
            $('#not-limit-category').prop('checked', false);
        }
    });
    
    var type = '{$type}';
    var canSelect = '{$canSelect}';
    if(type != '5' && canSelect)
    {
        //是否启用公司与部门
        $('#is_belong_company').click(function(){
            showBelongCompany();
        });
    }
    
    showBelongCompany();
    function showBelongCompany()
    {
        if($('#is_belong_company').is(':checked'))
        {
            $('.field-department_id').show();
            $('.field-company_id').show();
        }
        else
        {
             $('.field-department_id').hide();     
             $('.field-company_id').hide();     
        }
    }

JS
)?>