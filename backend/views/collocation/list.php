<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use yii\bootstrap\Html;
use yii\helpers\Url;

$this->title = '关联搭配商品';
$this->params['breadcrumbs'] = [
    ['label' => '商品管理', 'url' => ['product/list']],
    $this->title
];
/** @var \common\models\Product $product */
/** @var \yii\data\DataProviderInterface $provider */
/** @var [] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
?>

<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <?= $this->render('/product/nav-tabs', ['product' => $product]) ?>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <ul class="nav nav-tabs">
                            <li><a href="<?= Url::to(['product-related/list', 'id' => $product->id]) ?>">关联其他类型商品</a></li>
                            <?php if(!$product->isPayAfterService()):?>
                                <li><a href="<?= Url::to(['package-related/list', 'id' => $product->id]) ?>">关联套餐商品</a></li>
                                <?php if(!$product->isPackage()):?>
                                <li class="active"><a href="<?= Url::to(['collocation/list', 'id' => $product->id]) ?>">关联搭配商品</a></li>
                                <?php endif;?>
                            <?php endif;?>
                        </ul>
                        <div class="tab-content">
                            <p></p>
                            <a href="#" class="btn btn-primary btn-sm add-collocation-modal" data-target="#add-collocation-modal"
                               data-toggle="modal"><span class="fa fa-plus"></span> 新增</a>
                            <table class="footable table table-striped">
                                <thead>
                                <tr>
                                    <th>商品命名</th>
                                    <th>关联商品名称</th>
                                    <th>关联商品简介</th>
                                    <th class="text-right" data-sort-ignore="true">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($models as $model): ?>
                                    <tr>
                                        <td><?= $model['spec_name']; ?></td>
                                        <td><?= $model['name']; ?></td>
                                        <td><?= $model['desc']; ?></td>
                                        <td class="text-right">
                                                <span class="btn btn-xs btn-white delete-btn"
                                                      data-target="#delete-collocation-modal"
                                                      data-toggle="modal" data-product-id="<?= $model['product_id'] ?>"
                                                      data-collocation-product-id="<?= $model['collocation_product_id'] ?>">删除</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="4">
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
    </div>
</div>

<div class="modal fade" id="add-collocation-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $model = new \backend\models\CollocationForm();
    $model->product_id = $product->id;
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['collocation/add'],
        'validationUrl' => ['collocation/validation'],
        'id' => 'product-collocation-form',
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
                <h4 class="modal-title">新增搭配商品</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($model, 'collocation_product_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                    'serverUrl' => ['product/ajax-list', 'id' => $product->id],
                    'itemsName' => 'products',
                    'nameField' => 'name',
                    'searchKeywordName' => 'keyword',
                ]); ?>
                <?= $form->field($model, 'desc')->textarea(); ?>
            </div>
            <?= Html::activeHiddenInput($model, 'product_id') ?>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
            </div>
        </div>
    </div>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>

<div class="modal fade" id="delete-collocation-modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">删除搭配商品</h4>
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
    $('#add-collocation-modal').on('show.bs.modal', function (event) {
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
	    var id = $(this).attr('data-product-id');
	    var c_id = $(this).attr('data-collocation-product-id');
	    $('.sure-btn').on('click',function(){
	        $.post('" . \yii\helpers\Url::to(['remove']) . "',{p_id: id, c_id: c_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	            window.location.reload();
	        }
	    },'json')
	    })
	})
    $('.add-collocation-modal').on('click',function(){
        $('#product-collocation-form').trigger('reset.yiiActiveForm');
    });") ?>
