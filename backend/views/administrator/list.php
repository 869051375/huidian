<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use common\models\Administrator;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\Html;

$actionUniqueId = Yii::$app->controller->action->uniqueId;
/** @var \yii\data\DataProviderInterface $provider */
/** @var Administrator[] $models */
/** @var string $type */
/** @var \backend\models\AdministratorSearch $searchModel */
$models = $provider->getModels();
$pagination = $provider->getPagination();
$imageStorage = Yii::$app->get('imageStorage');
\toxor88\switchery\SwitcheryAsset::register($this);
$searchUrl= '';
$statusUrl = '';
$this->title = '';
if($actionUniqueId == 'administrator/list-manager')
{
    $searchUrl = ['administrator/list-manager'];
    $statusUrl = \yii\helpers\Url::to(['status-manager']);
    $this->title = '管理员管理';
}
elseif($actionUniqueId == 'administrator/list-customer-service')
{
    $searchUrl = ['administrator/list-customer-service'];
    $statusUrl = \yii\helpers\Url::to(['status-customer-service']);
    $this->title = '客服管理';
}
elseif($actionUniqueId == 'administrator/list-supervisor')
{
    $searchUrl = ['administrator/list-supervisor'];
    $statusUrl = \yii\helpers\Url::to(['status-supervisor']);
    $this->title = '嘟嘟妹管理';
}
elseif($actionUniqueId == 'administrator/list-salesman')
{
    $searchUrl = ['administrator/list-salesman'];
    $statusUrl = \yii\helpers\Url::to(['status-salesman']);
    $this->title = '业务员管理';
}
$this->params['breadcrumbs'] = [$this->title];
?>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?= $this->title; ?> </h5>
                <div class="ibox-tools">
                    <?php if ($actionUniqueId == 'administrator/list-manager' && Yii::$app->user->can('administrator/add-manager')): ?>
                        <a href="<?= \yii\helpers\Url::to(['administrator/add-manager', 'type' => Administrator::TYPE_ADMIN]);?>" class="btn btn-primary btn-sm"><span class="fa fa-plus"></span>添加管理员</a>
                    <?php endif;?>
                    <?php if ($actionUniqueId == 'administrator/list-customer-service' && Yii::$app->user->can('administrator/add-customer-service')): ?>
                        <a href="<?= \yii\helpers\Url::to(['administrator/add-customer-service', 'type' => Administrator::TYPE_CUSTOMER_SERVICE]);?>" class="btn btn-primary btn-sm"><span class="fa fa-plus"></span>添加客服</a>
                    <?php endif;?>
                    <?php if ($actionUniqueId == 'administrator/list-supervisor' && Yii::$app->user->can('administrator/add-supervisor')): ?>
                        <a href="<?= \yii\helpers\Url::to(['administrator/add-supervisor', 'type' => Administrator::TYPE_SUPERVISOR]);?>" class="btn btn-primary btn-sm"><span class="fa fa-plus"></span>添加嘟嘟妹</a>
                    <?php endif;?>
                    <?php if ($actionUniqueId == 'administrator/list-salesman' && Yii::$app->user->can('administrator/add-salesman')): ?>
                        <a href="<?= \yii\helpers\Url::to(['administrator/add-salesman', 'type' => Administrator::TYPE_SALESMAN]);?>" class="btn btn-primary btn-sm"><span class="fa fa-plus"></span>添加业务员</a>
                    <?php endif;?>
                </div>
            </div>
            <div class="ibox-content page-select2-area">
                <?php
                $labelOptions = ['labelOptions' => ['class' => false]];
                $form = \yii\bootstrap\ActiveForm::begin([
                    'action' => $searchUrl,
                    'layout' => 'inline',
                    'method' => 'get',
                ]); ?>
                <?= $form->field($searchModel, 'keyword', $labelOptions)->textInput(['placeholder'=>'请输入关键词']) ?>
                <?= $form->field($searchModel, 'department_id', $labelOptions)->widget(Select2Widget::className(), [
                    'serverUrl' => ['crm-department/ajax-list'],
                    'itemsName' => 'items',
                    'selectedItem' => $searchModel->department ? [$searchModel->department->id => $searchModel->department->name] : [],
                    'nameField' => 'name',
                    'searchKeywordName' => 'keyword',
                    'placeholder' => '请选择部门',
                    'placeholderId' => '0',
                    'options' => ['class' => 'form-control', 'prompt' => '请选择部门'],
                    'width' => '120px',
                ]); ?>
                <button type="submit" class="btn btn-default">搜索</button>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
            </div>

            <div class="ibox-content">
                <div class="table-responsive">
                    <?php if($type == 'manager'): ?>

                        <?= $this->render('manager-list', ['type' => $type, 'models' => $models, 'actionUniqueId' => $actionUniqueId, 'imageStorage' => $imageStorage, 'pagination' => $pagination])?>

                    <?php elseif($type == 'customer-service'): ?>

                        <?= $this->render('customer-service-list', ['type' => $type, 'models' => $models, 'actionUniqueId' => $actionUniqueId, 'imageStorage' => $imageStorage, 'pagination' => $pagination])?>

                    <?php elseif($type == 'supervisor'): ?>

                        <?= $this->render('supervisor-list', ['type' => $type, 'models' => $models, 'actionUniqueId' => $actionUniqueId, 'imageStorage' => $imageStorage, 'pagination' => $pagination])?>

                    <?php elseif($type == 'salesman'): ?>

                        <?= $this->render('salesman-list', ['type' => $type, 'models' => $models, 'actionUniqueId' => $actionUniqueId, 'imageStorage' => $imageStorage, 'pagination' => $pagination])?>

                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>
    <div class="modal fade" id="up-down-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">修改账号状态</h4>
                </div>
                <div class="modal-body">
                    确定禁用吗?
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
                modal.find('.warning-active').empty().text('');
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
    
    modal.find('.sure-btn').click(function(){
        changeStatus(currentCheckbox);
    });
    
    function changeStatus(checkbox)
    {
        var status = checkbox.isChecked() ? 0 : 1;
        $.post('{$statusUrl}', {status: status, id: $(checkbox.element).attr('data-id'), type: $(checkbox.element).attr('data-type')}, function(rs){
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
    <?php
    $this->registerJs(<<<JS
    // var statusList = document.querySelectorAll('.change-status-checkbox');
    // for(var i = 0; i < statusList.length; i++)
    // {
    //     new Switchery(statusList[i], {"size":"small","className":"switchery"});
    // }
JS
);?>

