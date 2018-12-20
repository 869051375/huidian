<?php

/* @var $this yii\web\View */
use backend\models\CrmCustomerForm;
use common\models\CrmCustomer;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var \common\models\CrmCustomer $customer */
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
$combine = $customer->getCombine($administrator);
?>
    <div class="row wrapper white-bg">
    <div class="ibox-content">
    <div class="row border-bottom " style="padding-bottom: 20px;">
        <div class="col-lg-7">
            <?php if($customer->user):  ?>
                <img class="img-circle" src="<?= $customer->user->getImageUrl(100,100) ?>" alt="<?= $customer->name ?>" >
            <?php endif; ?>
            <div style="display: inline-block;position: relative;">
	            <p class="m-l-sm business-name" style="font-size: 20px;"><?= $customer->name; ?></p>
	            <?php if ($customer->isProtect() && !$customer->isPublic()):?>
	                <span class="guard"></span>
	            <?php endif;?>
	        </div>
        </div>
        <?php if (!$customer->isPublic()):?>
        <?php endif;?>
    </div>
    <div class="row" style="padding-top:10px;">
            <table class="table no-margins">
                <thead>
                <tr class="no-borders">
                    <th class="no-borders">负责人</th>
                    <th class="no-borders">客户级别</th>
                    <th class="no-borders">获取方式</th>
                    <th class="no-borders">是否注册</th>
                </tr>
                </thead>
                <tbody>
                <tr class="no-borders">
                    <td class="no-borders">
                        <?php if ($customer->isPublic()):?>
                            无
                        <?php else:?>
                            <?php if(isset($customer->administrator)):  ?>
                                <?= $customer->administrator->name ?>&nbsp;
                            <?php endif; ?>
                            <?php if (Yii::$app->user->can('customer/*') && ($customer->isSubFor($administrator) || $customer->isPrincipal($administrator))): ?>
                                <a class="change-administrator" data-target="#change-administrator-modal" data-toggle="modal">修改</a>
                            <?php endif; ?>
                        <?php endif;?>
                    </td>
                    <td class="no-borders">
                        <?php if ($customer->isPublic()):?>
                            无
                        <?php else:?>
                            <?php if($combine):?>
                                <?= $combine->getLevelName();?>
                                <?php if (Yii::$app->user->can('customer/*') && $customer->isReceive() && ($customer->isSubFor($administrator) || $customer->isPrincipal($administrator) || $customer->isCombine($administrator))): ?>
                                    <a class="change-level" data-target="#change-level-modal" data-toggle="modal">修改</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <?= $customer->crmCombine ? $customer->crmCombine->getLevelName() : '--'; ?>
                            <?php endif; ?>
                        <?php endif;?>
                    </td>
                    <td class="no-borders"><?= $customer->getRegisterModeName(); ?></td>
                    <td class="no-borders"><?= $customer->isRegister() ? '是':'否'; ?></td>
                </tr>
                </tbody>
            </table>
    </div>
    </div>

<div class="col-md-12">
    <?php if (Yii::$app->user->can('customer/*') && ($customer->isSubFor($administrator) || $customer->isPrincipal($administrator) || $customer->isCombine($administrator))): ?>
    <!--更换负责人start-->
    <div class="modal fade" id="change-administrator-modal" role="dialog" aria-labelledby="change-administrator-label">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php
                $changeAdministratorForm = new \backend\models\CustomerDetailChangeAdministratorForm();
                $changeCompanyUrl = \yii\helpers\Url::to(['administrator/ajax-list', 'type' => \common\models\Administrator::TYPE_SALESMAN, 'company_id' => '__company_id__']);
                $form = \yii\bootstrap\ActiveForm::begin([
                    'action' => ['change-administrator'],
                    'id' => 'change-administrator-form',
                ]); ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="confirm-receive-label">更换负责人</h4>
                </div>
                <div class="modal-body">
                    <?= \yii\helpers\Html::activeHiddenInput($changeAdministratorForm, 'customer_id', ['value'=>$customer->id])?>
                    <?= $form->field($changeAdministratorForm, 'company_id')->widget(Select2Widget::className(),[
                        'serverUrl' => \yii\helpers\Url::to(['company/ajax-list']),
                        'itemsName' => 'company',
                        'selectedItem' =>  \yii\helpers\ArrayHelper::merge(['0' => '全部'],$changeAdministratorForm->company ? [$changeAdministratorForm->company->id => $changeAdministratorForm->company->name] : []),
                        'options' => ['class' => 'form-control', 'prompt'=>'全部'],
                        'nameField' => 'name',
                        'placeholderId' => '0',
                        'placeholder' => '全部',
                        'width' => '300px',
                        'eventSelect' => new JsExpression("
                        $('#customerdetailchangeadministratorform-administrator_id').val('0').trigger('change');
                    ")
                    ])->label('所属公司');?>
                    <?= $form->field($changeAdministratorForm, 'administrator_id')->widget(Select2Widget::className(), [
//                        'selectedItem' => $changeAdministratorForm->administrator ? [$changeAdministratorForm->administrator->id => $changeAdministratorForm->administrator->name] : [],
//                        'options' => ['class' => 'form-control', 'prompt'=>'请选择负责人'],
                        'nameField' => 'name',
//                        'placeholderId' => '0',
                        'placeholder' => '请选择负责人',
                        'searchKeywordName' => 'keyword',
                        'width' => '300px',
                        'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-list', 'type' => \common\models\Administrator::TYPE_SALESMAN, 'company_id' => '__company_id__']),
                        'itemsName' => 'items',
                        'eventOpening' => new JsExpression("
                            var id = $('#customerdetailchangeadministratorform-company_id').val();
                            serverUrl = '{$changeCompanyUrl}'.replace('__company_id__', id ? id : '0');
                        ")
                    ])->label('负 责 人');?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary sure-btn">确定</button>
                </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
                <?php $this->registerJs(<<<JS
            $.fn.select2.defaults.set('width', '80%');
            $('.change-administrator').click(function(){
                var id = $(this).parents('tr').attr('data-id');
                $('#change-administrator-id').val(id);
            });
            $('#change-administrator-form').on('beforeSubmit', function(){
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
                );?>
            </div>
        </div>
    </div>
    <!--更换负责人end-->

    <!--修改客户级别start-->
    <div class="modal fade" id="change-level-modal" role="dialog" aria-labelledby="change-level-label">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php
                $labelOptions = ['labelOptions' => ['class' => false]];
                $changeLevelForm = new \backend\models\CustomerCombineChangeLevelForm();
                $changeLevelForm->level = $combine ? $combine->level : 0;
                $form = \yii\bootstrap\ActiveForm::begin([
                    'action' => ['change-level'],
                    'id' => 'change-level-form',
                ]); ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="confirm-receive-label">修改客户级别</h4>
                </div>
                <div class="modal-body">
                    <?= \yii\helpers\Html::activeHiddenInput($changeLevelForm, 'customer_id', ['value'=> $combine ? $combine->customer_id :''])?>
                    <?= \yii\helpers\Html::activeHiddenInput($changeLevelForm, 'administrator_id', ['value'=>$combine ? $combine->administrator_id : ''])?>
                    <?= $form->field($changeLevelForm, 'level', $labelOptions)->dropDownList([
                        '' => '未选择',
                        '0' => '无效客户',
                        '1' => '有效客户',
                    ])?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary sure-btn">确定</button>
                </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
                <?php $this->registerJs(<<<JS
        $.fn.select2.defaults.set('width', '60%');
        $('.change-level').click(function(){
            var form = $('#change-level-form');
            form.find('.warning-active').text('');
            form.trigger('reset.yiiActiveForm');
        });
        $('#change-level-form').on('beforeSubmit', function(){
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
                );?>
            </div>
        </div>
    </div>
    <!--修改客户级别end-->
    <?php endif; ?>
</div>
    </div>

<?php $this->registerJs(<<<JS
$(".new-crate").mouseenter(function()
{
    $(".model-block").show();
}).mouseleave(function(){
    $(".model-block").hide();
});
$(".model-block").mouseenter(function(){
    $(this).show();
}).mouseleave(function(){
    $(".model-block").hide();
});
JS
);
?>

<!--客户保护begin-->
<div class="modal fade" id="protect-modal" role="dialog" aria-labelledby="protect-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php
            $protectForm = new \backend\models\CustomerProtectForm();
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['protect'],
                'id' => 'protect-form',
            ]); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="protect-label"></h4>
            </div>
            <div class="modal-body">
                确定保护吗？
            </div>
            <div class="modal-footer">
                <?= Html::activeHiddenInput($protectForm, 'customer_id', ['id' => 'protect-customer-id'])?>
                <?= Html::activeHiddenInput($protectForm, 'is_protect', ['id' => 'protect-is-protect'])?>
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary sure-btn">确定</button>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
            <?php $this->registerJs(<<<JS
            $('.confirm-protect').click(function(){
                $('.warning-active').text('');
                var id = $(this).attr('data-id');
                var isProtect = $(this).attr('data-protect');
                $('#protect-customer-id').val(id);
                $('#protect-is-protect').val(isProtect);
                var name = $(this).attr('data-name');
                $('#protect-form .modal-body').html('确认要保护“'+ name +'”吗？');
                $('#protect-form .modal-title').html('客户保护');
            });
            
            $('.cancel-protect').click(function(){
                $('.warning-active').text();
                var id = $(this).attr('data-id');
                var isProtect = $(this).attr('data-protect');
                $('#protect-customer-id').val(id);
                $('#protect-is-protect').val(isProtect);
                var name = $(this).attr('data-name');
                $('#protect-form .modal-body').html('确认要取消保护“'+ name +'”吗？');
                $('#protect-form .modal-title').html('取消客户保护');
            });
            $('#protect-form').on('beforeSubmit', function(){
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
            );?>
        </div>
    </div>
</div>
<!--客户保护end-->
