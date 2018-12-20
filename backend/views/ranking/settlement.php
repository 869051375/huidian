<?php
use common\models\MonthProfitRecord;
use yii\helpers\Url;
$lastModel = MonthProfitRecord::getLastRecord();
?>
<div class="ibox-tools">
    <?php if (Yii::$app->user->can('settlement_performance/*')): ?>
        <?php if($lastModel && ($lastModel->isPerformanceReady()||$lastModel->isPerformanceFinish())): ?>
            <a  class="btn btn-primary btn-sm" data-target="#settlement-modal"
                data-toggle="modal">
                <span class="fa fa-calendar"></span> 提成总计算</a>
        <?php elseif($lastModel && $lastModel->isPerformanceDoing()): ?>
            <a  class="btn btn-primary btn-sm">
                <span class="fa fa-calendar"></span> 提成金额计算中......</a>
        <?php else:?>
            <button  class="btn btn-primary btn-sm" disabled>
                <span class="fa fa-calendar" ></span> 提成金额计算</button>
        <?php endif; ?>
    <?php endif; ?>
</div>
<div class="modal fade" id="settlement-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">提成总计算</h4>
            </div>
            <div class="modal-body">
                <?php if($lastModel && $lastModel->isPerformanceReady()): ?>
                    <p class="text-center">确认要结算本月提成吗？</p>
                <?php elseif($lastModel && $lastModel->isPerformanceFinish()): ?>
                    <p class="text-center text-danger">当月提成计算已完成，请于次月预计利润总结算之后计算提成！</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary settlement-performance-btn">确定</button>
            </div>
        </div>
    </div>
</div>
<?php
$settlementUrl = Url::to(['settlement-performance/index']);
$this->registerJs(<<<JS
$('.settlement-performance-btn').click(function() 
{
    $.post('{$settlementUrl}',{},function(rs) 
    {
        if(rs.status == 200)
        {
            if(rs.settlement_status == 1)
            {
                window.location.reload();
            }else if(rs.settlement_status == 2)
            {
                $('#settlement-modal').modal('hide');
            }
        }
    })
  
})
JS
)
?>