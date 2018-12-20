<?php
/* @var $this yii\web\View */

use yii\bootstrap\Html;

$this->title = '套餐商品设置';
$this->params['breadcrumbs'] = [
    ['label' => '套餐商品列表', 'url' => ['product/package-list']],
    $this->title
];
/** @var \common\models\Product $product */
/** @var \yii\data\DataProviderInterface $provider */
/** @var [] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
?>
<?php \backend\assets\SortAsset::register($this); ?>
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <?= $this->render('/product/package-nav-tabs', ['product' => $product]) ?>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <div class="tab-content">
                            <table class="footable table table-striped">
                                <tbody>
                                <!--排序-->
                                <div class="ibox">
                                        <div class="ibox-content">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="col-xs-3"><h3>一级分类</h3></div>
                                                    <div class="col-xs-3"><h3>二级分类</h3></div>
                                                    <div class="col-xs-3"><h3>商品名称</h3></div>
                                                    <div class="col-xs-1"><h3>排序</h3></div>
                                                    <div class="col-xs-2 text-right"><h3>操作</h3></div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="list-group sortablelist">
                                                        <?php if (empty($models)): ?>
                                                            <div class="alert alert-info">暂无套餐商品</div>
                                                        <?php endif; ?>
                                                        <?php foreach ($models as $model): ?>
                                                            <li class="list-group-item so1 sortableitem" data-id="<?= $model['product_id']?>">
                                                                <div class="row">
                                                                    <div class="col-xs-3">
                                                                        <?= $model['top_category_name']; ?>
                                                                    </div>
                                                                    <div class="col-xs-3">
                                                                        <?= $model['category_name']; ?>
                                                                    </div>
                                                                    <div class="col-xs-3">
                                                                        <?= $model['name']; ?>
                                                                    </div>
                                                                    <?php if (Yii::$app->user->can('product/update')): ?>
                                                                        <div class="col-xs-1">
                                                                            <span class="btn btn-xs btn-link move-up">
                                                                                <i class="glyphicon glyphicon-arrow-up"></i></span>
                                                                            <span class="btn btn-xs btn-link move-down">
                                                                                <i class="glyphicon glyphicon-arrow-down"></i></span>
                                                                        </div>
                                                                        <?php if (!$product->isConfirmed()): ?>
                                                                        <div class="col-xs-2 text-right">
                                                                            <?php if (!$product->isConfirmed()): ?>
                                                                                <span class="btn btn-xs btn-white delete-btn"
                                                                                      data-target="#delete-package-product-modal"
                                                                                      data-toggle="modal"
                                                                                      data-package-id="<?= $model['package_id'] ?>"
                                                                                      data-package-product-id="<?= $model['product_id'] ?>">删除</span>
                                                                            <?php endif;?>
                                                                        </div>
                                                                    <?php endif;?>
                                                                    <?php endif;?>
                                                                </div>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <?php if (Yii::$app->user->can('product/update')): ?>
                                                        <?php if (!$product->isConfirmed()): ?>
                                                        <a href="#" class="btn btn-primary btn-sm add-package-product-modal" data-target="#add-package-product-modal"
                                                           data-toggle="modal"><span class="fa fa-plus"></span> 添加</a>
                                                        <button class="btn btn-sm btn-primary flow-publish" data-target="#flow-publish-modal"
                                                                data-toggle="modal">确认套餐商品
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php endif;?>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                <!--排序-->
                                </tbody>
                                <tfoot>

                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--添加套餐商品start-->
<div class="modal fade" id="add-package-product-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $model = new \backend\models\PackageProductForm();
    $model->package_id = $product->id;
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['package-product/create'],
        'validationUrl' => ['package-product/validation'],
        'id' => 'package-product-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-2',
                'offset' => 'col-sm-offset-2',
                'wrapper' => 'col-sm-8',
            ],
        ],
    ]); ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">新增套餐商品</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($model, 'product_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                    'serverUrl' => ['package-product/ajax-list'],
                    'itemsName' => 'products',
                    'nameField' => 'name',
                    'searchKeywordName' => 'keyword',
                ]); ?>
            </div>
            <?= Html::activeHiddenInput($model, 'package_id') ?>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
            </div>
        </div>
    </div>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<!--添加套餐商品end-->
<!--删除套餐商品start-->
<div class="modal fade" id="delete-package-product-modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">删除套餐商品</h4>
            </div>
            <div class="modal-body">
                确定删除吗?
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary delete-sure-btn">确定</button>
            </div>
        </div>
    </div>
</div>
<!--删除套餐商品end-->
<!--确认套餐商品start-->
<?php if (Yii::$app->user->can('product/update')): ?>
    <div class="modal fade" id="flow-publish-modal" role="dialog" aria-labelledby="modal-title">
        <?php
        $confirmForm = new \backend\models\PackageProductConfirmForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['package-product/ajax-confirm', 'id' => $product->id],
            'id' => 'package-product-confirm-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-3',
                    'offset' => 'col-sm-offset-3',
                    'wrapper' => 'col-sm-8',
                    'hint' => 'col-sm-offset-3 col-sm-8'
                ],
            ],
        ]); ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">确认套餐商品</h4>
                </div>

                <div class="modal-body input_box">
                    <p>确认后本套餐下的商品不可添加及删除，是否确认？</p>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary add-sure-btn">确认</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>
<?php endif;?>
<!--确认套餐商品end-->
<?php
$sortUrl = \yii\helpers\Url::to(['ajax-sort', 'package_id' => $product->id]);
$this->registerJs("
    
    $('#add-package-product-modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');
        var modal = $(this);
        modal.find('.modal-title').text(recipient);
    });

    $('.cancel-btn').on('click',function(){
        $('.warning-active').html('');
    })
    $.fn.select2.defaults.set('width', '100%');
	$('.delete-btn').on('click',function(){
	    var delete_id = $(this).attr('data-package-id');
	    var related_delete_id = $(this).attr('data-package-product-id');
	    $('.delete-sure-btn').on('click',function(){
	        $.post('" . \yii\helpers\Url::to(['delete']) . "',{package_id:delete_id,product_id:related_delete_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	        
	            window.location.reload();
	        }
	    },'json')
	    })
	})
    $('.add-package_product-modal').on('click',function(){
        $('#package-product-form').trigger('reset.yiiActiveForm');
    });
    
    //套餐商品上下移动换位置
    $('.sortablelist').clickSort({
            speed:200,
            moveCallback: function(source_id, target_id){
            $.post('{$sortUrl}', {source_id: source_id, target_id: target_id}, function(rs){
            }, 'json');
        },
            callback:function(){
                setTimeout(function(){
                    $('.sortableitem').find('.move-up,.move-down').show();
                    var div1 = $('.so1:first');
                    var div2 = $('.so1:last');
                    div1.find('.move-up').hide();
                    div2.find('.move-down').hide();
                }, 30);
            }
    
        });
        $('.sortablelist').find('.move-up,.move-down').show();
        var div1 = $('.so1:first');
        var div2 = $('.so1:last');
        div1.find('.move-up').hide();
        div2.find('.move-down').hide();
    
	");
?>
