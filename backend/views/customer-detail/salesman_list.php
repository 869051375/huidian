<?php

/* @var $this yii\web\View */
/* @var $user */
?>
<?php
/* @var $this yii\web\View */
use common\models\CrmCustomerCombine;
use common\models\CrmOpportunity;

/** @var \common\models\Administrator $admin */
$administrator = Yii::$app->user->identity;
/* @var CrmCustomerCombine $model[] */
?>

<div class="wrapper wrapper-content animated fadeIn">
    <?= $this->render('info', ['customer' => $customer]) ?>
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <?= $this->render('nav-tabs', ['customer' => $customer]) ?>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>合伙人姓名</th>
                                <th>所属公司</th>
                                <th>部门/子部门</th>
                                <th>客户级别</th>
                                <th>关联订单</th>
                                <th>关联商机</th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php /**@var CrmCustomerCombine[] $model **/ ?>
                            <?php foreach($model as $key => $item): ?>
                                <tr>
                                    <td><?= $key+1; ?></td>
                                    <td>
                                        <?php if($item->administrator): ?>
                                        <?= $item->administrator->name ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($item->company): ?>
                                            <?= $item->company->name ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($item->crmDepartment): ?>
                                            <?= $item->crmDepartment->name ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $item->getLevelName(); ?></td>
                                    <td>
                                        <?= '已付：'.$item->getOrderAlreadyAmount(); ?><br>
                                        <?= '未付：'.$item->getOrderUnpaidAmount(); ?>
                                    </td>
                                    <td>
                                        <?= '已成交：'.$item->getOpportunityAmount(CrmOpportunity::STATUS_DEAL); ?><br>
                                        <?= '未成交：'.$item->getOpportunityAmount(CrmOpportunity::STATUS_APPLY); ?><br>
                                        <?= '已失败：'.$item->getOpportunityAmount(CrmOpportunity::STATUS_FAIL); ?><br>
                                        <?= '待确认：'.$item->getOpportunityNoReceive(); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="delete_Carousel" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">删除所属合伙人</h4>
            </div>
            <div class="modal-body">
                确定删除吗?
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
$this->registerJs("
    $('.cancel-btn').on('click',function(){
        $('.warning-active').html('');
    })
	$('.delete-btn').on('click',function(){
	    var customer_id = $(this).attr('data-customer-id');
	    var administrator_id = $(this).attr('data-administrator-id');
	    $('.sure-btn').unbind();
	    $('.sure-btn').on('click',function(){
	        $.post('" . \yii\helpers\Url::to(['ajax-delete', 'is_validate' => '1']) . "',{customer_id:customer_id, administrator_id:administrator_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	            window.location.reload();
	        }
	    },'json')
	    })
	}) 
    ");
?>
