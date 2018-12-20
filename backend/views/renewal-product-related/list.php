<?php

/* @var $this yii\web\View */
use backend\widgets\LinkPager;
use yii\bootstrap\Html;
/* @var $provider yii\data\ActiveDataProvider */
$this->params['breadcrumbs'][] = '关联续费商品列表';
/** @var \common\models\RenewalProductRelated[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
\toxor88\switchery\SwitcheryAsset::register($this);
?>
    <div class="row">
        <div class="col-xs-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>关联续费商品列表 </h5>
                    <div class="ibox-tools">
                        <?php if (Yii::$app->user->can('renewal-product-related/create')): ?>
                            <a href="<?= \yii\helpers\Url::to(['create']) ?>" class="btn btn-primary btn-sm"><span
                                        class="fa fa-plus"></span> 新增</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ibox-content">
                    <ul class="list-group sortablelist">
                        <li class="list-group-item">
                            <div class="row">
                                <span class="col-xs-2">名称</span>
                                <span class="col-xs-3">备注说明</span>
                                <span class="col-xs-5">包含商品</span>
                                <span class="col-xs-1">状态</span>
                                <span class="col-xs-1 text-right">操作</span>
                            </div>
                        </li>
                        <?php foreach ($models as $model):
                            $options = [
                                'id' => false,
                                'class' => 'change-status-checkbox',
                                'label' => false,
                                'data-id' => $model->id,
                            ];
                            if(!Yii::$app->user->can('renewal-product-related/status') || empty($model->getProductIds()))
                            {
                                $options['readonly'] = 'readonly';
                            }
                            ?>
                            <li class="list-group-item so1 sortableitem" data-id="<?= $model->id?>">
                                <div class="row">
                                    <div class="col-xs-2">
                                        <?= $model->name?>
                                    </div>
                                    <div class="col-xs-3">
                                        <?= $model->remark?>
                                    </div>
                                    <div class="col-xs-5">
                                        <ul class="list-unstyled">
                                            <?php foreach($model->getProductList() as $product): ?>
                                                <span class="label label-default del-product" style="cursor:pointer;"><?= $product->name; ?></span>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="col-xs-1">
                                        <label>
                                            <?= Html::activeCheckbox($model, 'status', $options);?>
                                        </label>
                                    </div>
                                    <div class="col-xs-1 text-right" data-id="">
                                        <?php if (Yii::$app->user->can('renewal-product-related/update')): ?>
                                            <a class="btn btn-xs btn-white update-btn" href="<?= \yii\helpers\Url::to(['renewal-product-related/create', 'id' => $model->id]) ?>"> 编辑</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                    <div class="row">
                        <div class="margin-auto">
                            <?=
                            LinkPager::widget([
                                'pagination' => $pagination
                            ]);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php \backend\assets\SortAsset::register($this); ?>

<!--上下线start-->
<?php if(Yii::$app->user->can('renewal-product-related/status')): ?>
    <div class="modal fade" id="up-down-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">关联续费商品状态修改</h4>
                </div>
                <div class="modal-body">
                    点击【确定】即为生效
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary sure-btn">确定</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<!--上下线end-->

<?php
$ajaxStatusUrl = \yii\helpers\Url::to(['status']);
$this->registerJs(<<<JS
    var currentCheckbox = null;
    var statusList = document.querySelectorAll('.change-status-checkbox');
    var statusSwitchery = null;
    var modal = $('#up-down-modal');
    for(var i = 0; i < statusList.length; i++)
    {
        statusSwitchery = new Switchery(statusList[i], {"size":"small","className":"switchery change-flow-status"});
        (function (checkbox){
            $(checkbox.element).click(function(){
                var status = checkbox.isChecked() ? 0 : 1;
                if(status === 0)
                {
                    modal.find('.modal-body').text('点击【确定】即为生效');
                }
                else
                {
                    modal.find('.modal-body').text('点击【确定】即为失效');
                }
                modal.modal('show');
                currentCheckbox = checkbox;
                return false;
            });
        })(statusSwitchery);
    }
    
    modal.find('.sure-btn').click(function(){
        changeProductStatus(currentCheckbox);
    });
    
    function changeProductStatus(checkbox)
    {
        var status = checkbox.isChecked() ? 0 : 1;
        $.post('{$ajaxStatusUrl}', {status: status, id: $(checkbox.element).attr('data-id')}, function(rs){
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
    
    $('.change-flow-status').click(function() {
      modal.find('.warning-active').empty().text('');
    });
JS
);
?>
