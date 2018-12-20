<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use yii\helpers\Html;

/** @var \yii\data\DataProviderInterface $provider */
/* @var $roles backend\models\AdministratorRole[] */
/* @var $pagination yii\data\Pagination */
$roles = $provider->getModels();
$pagination = $provider->getPagination();

$this->title = '角色管理';
$this->params['breadcrumbs'] = [
    $this->title
];
\toxor88\switchery\SwitcheryAsset::register($this);
?>
<div class="wrapper wrapper-content animated fadeIn">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>角色列表 </h5>
                    <div class="ibox-tools">
                        <?php if (Yii::$app->user->can('role/create')): ?>
                            <a href="<?= \yii\helpers\Url::to(['create']) ?>" class="btn btn-primary btn-sm"><span
                                        class="fa fa-plus"></span> 添加角色</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ibox-content">

                    <table class="table table-stripped toggle-arrow-tiny" data-page-size="15">
                        <thead>
                        <tr>

                            <th>角色名称</th>
                            <th>角色类型</th>
                            <th>状态</th>
                            <th class="text-right" data-sort-ignore="true">操作</th>

                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($roles as $role):
                            $options = [
                                'id' => false,
                                'class' => 'change-status-checkbox',
                                'label' => false,
                                'data-id' => $role->id,
                            ];
                            ?>
                            <tr>
                                <td>
                                    <?= $role->name ?>
                                </td>
                                <td>
                                    <?= $role->getRoleType() ?>
                                </td>
                                <td>
                                    <label>
                                        <?= Html::activeCheckbox($role, 'status', $options); ?>
                                    </label>
                                </td>
                                <td class="text-right">
                                    <?php if (Yii::$app->user->can('role/update')): ?>
                                        <div class="btn-group">
                                            <a class="btn-white btn btn-xs"
                                               href="<?= yii\helpers\Url::to(['member', 'id' => $role->id]) ?>">成员</a>
                                            <a class="btn-white btn btn-xs"
                                               href="<?= yii\helpers\Url::to(['update', 'id' => $role->id]) ?>">编辑</a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3">
                                <?=
                                LinkPager::widget([
                                    'pagination' => $pagination
                                ]);
                                ?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>

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
                <h4 class="modal-title" id="myModalLabel">修改角色状态</h4>
            </div>
            <div class="modal-body">
                确定下线吗?
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
                    modal.find('.modal-body').text('确定下线吗？');
                }
                else
                {
                    modal.find('.modal-body').text('确定上线吗？');
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
        $.post('{$statusUrl}', {status: status, role_id: $(checkbox.element).attr('data-id')}, function(rs){
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

